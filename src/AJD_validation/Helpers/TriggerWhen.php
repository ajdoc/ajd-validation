<?php 

namespace AJD_validation\Helpers;

use AJD_validation\AJD_validation;
use AJD_validation\Contracts\Validation_interface;
use AJD_validation\Contracts\Trigger_when_interface;
use AJD_validation\Contracts\Validator;
use AJD_validation\Helpers\Logics_map;
use AJD_validation\Helpers\Array_helper;
use AJD_validation\Async\PromiseValidator;
use AJD_validation\Async\PromiseNull;

class TriggerWhen implements Validation_interface, Trigger_when_interface
{
	protected $ajd;
	protected $checker;
	protected $currentPromise;
	protected $promiseNull;

	public function __construct(AJD_validation $ajd, $checker)
	{
		$this->ajd = $ajd;
		$this->checker = $checker;
		$this->promiseNull = new PromiseNull;
	}

	public function checker($value = null, $checker = null)
	{
		if(empty($checker))
		{
			$checker = $this->checker;
		}

		return $this->processChecker($checker, $value);
	}

	public function runChecker($value = null, $checker = null)
	{
		if(!$this->checker($value, $checker))
		{
			$this->ajd->resetTriggerWhen();	
			return false;
		}

		return true;
	}

	protected function checkCheckerType($checker)
	{
		if(is_array($checker))
		{
			$checker = [$checker[0], $checker[1]];
		}

		if(
			!is_bool($checker)
			&&
			!is_callable($checker)
			&&
			!$checker instanceof Validator
			&&
			!$checker instanceof Logics_map
		)
		{
			throw new \InvalidArgumentException('Invalid Checker type.');
		}
	}

	protected function processChecker($checker, $value = null)
	{
		$this->checkCheckerType($checker);

		$defaultArgs = [];

		$defaultArgs[] = $this->ajd;

		$callable = $checker;

		if(is_array($checker))
		{
			$countChecker = count($checker);

			$callable = [$checker[0], $checker[1]];

			if($countChecker > 2)
			{
				$callableArgs = Array_helper::where($checker, function($value, $key)
				{
					return $key > 1;
				});

				if(!empty($callableArgs))
				{
					$defaultArgs = array_merge($defaultArgs, $callableArgs);
				}
			}
		}

		if(is_bool($checker))
		{
			return $checker;
		}
		else if($checker instanceof Validator)
		{
			return $checker->validate($value);
		}
		else if($checker instanceof Logics_map)
		{
			return $checker->deferToWhen()->runLogics($value, [], false);
		}
		else if(is_callable($callable))
		{
			return (bool) call_user_func_array($callable, $defaultArgs);
		}

		return false;
	}

	public function check($field, $value = null, $check_arr = true)
	{
		if($this->runChecker($value))
		{
			return $this->setPromise($this->ajd->check($field, $value, $check_arr))->getPromise();
		}

		return $this->getPromiseNul();
	}

	public function checkAsync($field, $value = null, $function = null, $check_arr = true)
	{
		if($this->runChecker($value))
		{
			return  $this->setPromise($this->ajd->checkAsync($field, $value, $function, $check_arr))->getPromise();
		}

		return $this->getPromiseNul();
	}

	public function checkDependent($field, $value = null, $origValue = null, array $customMessage = [], $check_arr = true)
	{
		if($this->runChecker($value))
		{
			return $this->setPromise($this->ajd->checkDependent($field, $value, $origValue, $customMesage, $check_arr))->getPromise();
		}

		return $this->getPromiseNul();
	}

	public function checkArr($field, $value, array $customMesage = [], $check_arr = true)
	{
		if($this->runChecker($value))
		{
			return $this->setPromise($this->ajd->checkArr($field, $value, $customMesage, $check_arr))->getPromise();
		}

		return $this->getPromiseNul();
	}

	public function checkGroup(array $data)
	{
		if($this->runChecker($data))
		{
			return $this->setPromise($this->ajd->checkGroup($data))->getPromise();
		}

		return $this->getPromiseNul();
	}

	public function middleware($name, $field, $value = null, $check_arr = true)
	{
		if($this->runChecker($value))
		{
			return $this->setPromise($this->ajd->middleware($name, $field, $value, $check_arr))->getPromise();
		}

		return $this->getPromiseNul();
	}

	public function checkAllMiddleware($field, $value = null, array $customMesage = [], $check_arr = true)
	{
		if($this->runChecker($value))
		{
			return $this->setPromise($this->ajd->checkAllMiddleware($field, $value, $customMesage, $check_arr))->getPromise();
		}

		return $this->getPromiseNul();
	}

	public function setPromise(PromiseValidator $promise)
	{
		$this->currentPromise = $promise;

		return $this;
	}

	public function getPromise() : PromiseValidator
	{
		return $this->currentPromise;
	}

	public function getPromiseNul() : PromiseNull
	{
		return $this->promiseNull;
	}
}