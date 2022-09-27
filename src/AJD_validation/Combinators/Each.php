<?php 

namespace AJD_validation\Combinators;

use AJD_validation\Async\ValidationResult;
use AJD_validation\Async\PromiseHelpers;
use AJD_validation\Async\Promise_interface;
use AJD_validation\AJD_validation as v;
use AJD_validation\Helpers\Array_helper;
use Closure;

final class Each 
{
	protected $rules;
	protected $context;
	protected $ajd;

	protected static $defaultParentErrorMessage = [];

	public function __construct(array $rules, $context = null)
	{
		$this->rules = $rules;
		$this->context = $context;
		$this->ajd = v::get_ajd_instance();
	}

	public function getContext()
	{
		return $this->context;
	}

	public function check(array $values, $parentField = null)
	{
		$promises = $this->processRules($values, $parentField);

		if(empty($promises))
		{
			return [];
		}

		if(!is_null($parentField))
		{
			return $promises;
		}

		$allPromise = PromiseHelpers::all($promises);

		$obs = v::get_observable_instance();

		$obs->attach_observer( 'passed', $allPromise, array( $this->ajd ) );
		$obs->attach_observer( 'fails', $allPromise, array( $this->ajd ) );

		$isValid = true;

		$allPromise->then(function() use(&$obs)
		{
			$obs->notify_observer( 'passed' );
		},
		function() use(&$obs, &$isValid)
		{
			$isValid = false;
			$obs->notify_observer( 'fails' );
		});

		$allValidationResult = $this->processResult($promises, $isValid);

		$allPromise->setValidationResult($allValidationResult);

		return $allPromise;
	}

	public function commonFirstValMapErrors($messages, $self)
	{
		$field = $self->getField();
		
		if(!isset($messages[$field]))
		{
			return [
				$field => $messages
			];
		}
		else
		{
			return $messages;
		}
	}

	protected function commonMapValues($value, $field)
	{
		if(!is_array($value))
		{
			return [$field => $value];
		}
		else
		{
			$countArr = count($value);

			if( $countArr == 1 && !Array_helper::isAssoc($value) )
			{
				return [$field => $value[0]];
			}

			return [ $field => $value ];
		}
	}

	protected function processResult(array $promises, $isValid)
	{
		$that = $this;
		
		$firstPromise = $promises[0];

		$firstValidation = $firstPromise->getValidationResult();

		$firstValidation = $firstValidation->setIsValid($isValid);

		$firstValidation = $firstValidation->mapErrors(function($messages, $self) use($that)
		{
			return $that->commonFirstValMapErrors($messages, $self);
		});

		$firstValidation = $firstValidation->mapValue(function($value, $self) use($that)
		{
			$field = $self->getField();

			return $that->commonMapValues($value, $field);
		});

		unset($promises[0]);
		$promises = array_values($promises);
		
		foreach($promises as $promise)
		{
			$validationResult = $promise->getValidationResult();
			
			$field = $validationResult->getField();

			$firstValidation = $firstValidation
			->join(
				$validationResult,
				function($valueA, $valueB) use($that, $field)
				{
					$valueBProcess = $that->commonMapValues($valueB, $field);
					
					return array_merge($valueA, $valueBProcess);
				},
				function($errorsA, $errorsB) use($field)
				{
					if(!empty($errorsB))
					{
						$errorsBMain = [
							$field => $errorsB
						];

						return array_merge($errorsA, $errorsBMain);
					}

					return $errorsA;
				}
			);

			$firstValidation->appendAjdProp($validationResult->getAjdProp());
		}

		$firstValidation->setIsValid($isValid);

		return $firstValidation;
	}

	protected function processRules(array $values, $parentField = null)
	{
		$context = $this->getContext();

		$cnt = 0;

		$ajd = $this->ajd;

		$promises = [];

		foreach($values as $field => $val)
		{
			$realField = $field;

			if(!is_null($parentField))
			{
				$realField = $parentField.'.'.$field;
			}

			foreach($this->rules as $ruleIndex => $rule)
			{
				$checkArr = true;

				if(is_array($val))
				{
					$checkArr = false;
				}
				
				if( $rule instanceof Closure ) 
				{
					$closure = $rule->bindTo($this, self::class);

					$result = $closure($ajd, $val, $field, $realField, $cnt, $ruleIndex, $values, $parentField);

					if(!empty($result))
					{
						$promises = $this->processRuleType($result, $field, $realField, $val, $checkArr, $promises);
					}
				}
				else
				{
					$promises = $this->processRuleType($rule, $field, $realField, $val, $checkArr, $promises);
				}
			}

			$cnt++;
		}

		return $promises;
	}

	protected function processRuleType($rule, $field, $realField, $val, $checkArr, array $promises = [])
	{
		if($rule instanceof ValidationResult)
		{
			$fieldVal = $rule->getField();

			if($fieldVal == $field)
			{
				$ruleDefinition = $rule->getValidationDefinition();

				$result = $ruleDefinition()->check($realField, $val, $checkArr);

				if(method_exists($result, 'getPromise'))
				{
					$promise = $result->getPromise();	
				}
				else
				{
					$promise = $result;
				}

				$promises[] = $promise;	
			}
		}
		else if($rule instanceof self)
		{
			if(is_array($val))
			{
				$result = $rule->check($val, $field);

				if(!empty($result))
				{
					$result = (!is_array($result)) ? [$result] : $result;
					$promises = array_merge($promises, $result);
				}
			}
		}
		else if($rule instanceof Promise_interface)
		{
			$promises[] = $rule;	
		}

		return $promises;
	}
}