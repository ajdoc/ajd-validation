<?php 

namespace AJD_validation\Validators;

use AJD_validation\AJD_validation;
use AJD_validation\Contracts\Validation_interface;
use AJD_validation\Async\PromiseValidator;

class DebugValidator implements Validation_interface
{
	protected $ajd;
	protected $currentPromise;
	protected $promiseNull;

	private $collectedData = [];

	public function __construct(AJD_validation $ajd)
	{
		$this->ajd = $ajd;
	}

	public function getCollectedData()
    {
        return $this->collectedData;
    }

    public function reset()
    {
        $this->collectedData = [];

        return $this;
    }

	public function debug()
	{
		$trace = debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS, 7);

        $file = $trace[0]['file'];
        $line = $trace[0]['line'];

        for ($i = 1; $i < 7; ++$i) {
            if (isset($trace[$i]['class'], $trace[$i]['function'])
                && 'validate' === $trace[$i]['function']
                && is_a($trace[$i]['class'], ValidatorInterface::class, true)
            ) {
                $file = $trace[$i]['file'];
                $line = $trace[$i]['line'];

                while (++$i < 7) {
                    if (isset($trace[$i]['function'], $trace[$i]['file']) && empty($trace[$i]['class']) && !str_starts_with($trace[$i]['function'], 'call_user_func')) {
                        $file = $trace[$i]['file'];
                        $line = $trace[$i]['line'];

                        break;
                    }
                }
                break;
            }
        }

        $name = str_replace('\\', '/', $file);
        $name = substr($name, strrpos($name, '/') + 1);

        $this->collectedData[] = [
            'caller' => compact('name', 'file', 'line'),
            // 'context' => compact('value', 'constraints'),
        ];

	}

	public function printCollectedData()
	{
		echo '<pre>';
		print_r($this->getCollectedData());

		return $this;
	}

	public function check($field, $value = null, $check_arr = true)
	{
		$this->debug();

		return $this->setPromise($this->ajd->check($field, $value, $check_arr));
	}

	public function checkAsync($field, $value = null, $function = null, $check_arr = true)
	{
		return  $this->setPromise($this->ajd->checkAsync($field, $value, $function, $check_arr))->getPromise();
	}

	public function checkDependent($field, $value = null, $origValue = null, array $customMessage = [], $check_arr = true)
	{
		return $this->setPromise($this->ajd->checkDependent($field, $value, $origValue, $customMesage, $check_arr))->getPromise();
	}

	public function checkArr($field, $value, array $customMesage = [], $check_arr = true)
	{
		return $this->setPromise($this->ajd->checkArr($field, $value, $customMesage, $check_arr))->getPromise();
	}

	public function checkGroup(array $data)
	{
		return $this->setPromise($this->ajd->checkGroup($data))->getPromise();
		
	}

	public function middleware($name, $field, $value = null, $check_arr = true)
	{
		return $this->setPromise($this->ajd->middleware($name, $field, $value, $check_arr))->getPromise();
		
	}

	public function checkAllMiddleware($field, $value = null, array $customMesage = [], $check_arr = true)
	{
		return $this->setPromise($this->ajd->checkAllMiddleware($field, $value, $customMesage, $check_arr))->getPromise();

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
}