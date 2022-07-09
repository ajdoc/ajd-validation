<?php namespace AJD_validation\Contracts;

use AJD_validation\Contracts\Abstract_rule;
use AJD_validation\Contracts\Invokable_rule_interface;
use AJD_validation\Contracts\Abstract_exceptions;

abstract class Abstract_invokable extends Abstract_rule implements Invokable_rule_interface
{
	protected $exception;

	public function setException(Abstract_exceptions $exception)
	{
		$this->exception = $exception;
	}

	public function run($value, $satisfier = NULL, $field = NULL)
    {
        
    }

    public function validate($value)
    {
        
    }
}