<?php namespace AJD_validation\Contracts;

use AJD_validation\AJD_validation;
use AJD_validation\Contracts\DataSetInterface;
use AJD_validation\Contracts\Abstract_exceptions;
use AJD_validation\Contracts\Rule_interface;

abstract class AbstractDataSet implements DataSetInterface
{
	protected $options = [];

	protected $name;
	protected $exception;
	public $checkValidation = true;

	protected $qualifiedErrorMessage;
	protected $exceptionMessages = [];

	protected $setPreValidate = [
		'value' => null,
		'field' => null,
		'check_arr' => true
	];

	public function setName($name)
	{
		$this->name = $name;
	}

	public function getName()
	{
		if(empty($this->name))
		{
			$this->name = self::class;
		}

		return $this->name;
	}

	public function getException()
	{
		$anonClass = new class() extends Abstract_exceptions
		{
			public static $defaultMessages = [];
		};

		$this->exception = $anonClass;

		return $this->exception;
	}

	public function appendError($errors)
	{
		$errors = (!is_array($errors)) ? ['default' => $errors] : $errors;
		
		$this->setExceptionMessages($errors);

		if(empty($this->orig_field))
		{
			$this->checkValidation = false;
			return false;
		}

		$this->checkValidation = false;
	}

	public function addError($errors, $key = null, $rules_name = null, $field = null, $clean_field = null, $check_arr = false)
	{
		$this->setErrorMessage($errors);

		if(empty($this->orig_field))
		{
			$this->checkValidation = false;
			return false;
		}

		$this->ajd->append_error_msg($errors, $field ?? $this->orig_field, $clean_field ?? $this->clean_field, $rules_name ?? $this->getName(), $this->check_arr, $key ?? 0);

		$this->checkValidation = false;
	}

	public function setErrorMessage($errorMessage)
	{
		$this->qualifiedErrorMessage = $errorMessage;
	}

	public function getErrorMessage()
	{
		return $this->qualifiedErrorMessage;
	}

	public function setExceptionMessages(array $errorMessage)
	{
		$this->exceptionMessages = $errorMessage;
	}

	public function getExceptionMessages()
	{
		return $this->exceptionMessages;
	}

	public function field()
	{
		return null;
	}

	public function preValidate($value = null, $field = null, $check_arr = true)
	{
		return [
			'value' => $value,
			'field' => $field,
			'check_arr' => $check_arr
		];
	}

	public function setPreValidate(array $preValidate)
	{
		$this->setPreValidate = array_merge($this->setPreValidate, $preValidate);
	}

	public function getPreValidate()
	{
		return $this->setPreValidate;
	}

	public function rules()
	{
		return null;
	}

	public function validation($value = null, $key = null)
	{
		return false;
	}

	public function __set($name, $value)
    {
    	$this->options[$name] = $value;
    }

    public function __get($name)
    {
    	if (array_key_exists($name, $this->options)) 
    	{
            return $this->options[$name];
        }

        return null;
    }

    public function __isset($name)
   	{
   		return isset($this->options[$name]);
   	}
}