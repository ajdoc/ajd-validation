<?php namespace AJD_validation\Contracts;

use AJD_validation\AJD_validation as v;
use AJD_validation\Contracts\Abstract_rule;
use AJD_validation\Contracts\Abstract_anonymous_rule_exception;
use AJD_validation\Contracts\Invokable_rule_interface;


abstract class Abstract_anonymous_rule extends Abstract_rule implements Invokable_rule_interface
{
	abstract public static function getAnonName();

    abstract public static function getAnonExceptionMessage(Abstract_exceptions $exceptionObj);

    public function __invoke($value, $satisfier = NULL, $field = NULL)
    {
        return $this->run($value, $satisfier, $field);
    }

    public function run($value, $satisfier = NULL, $field = NULL)
    {

    }

    public function validate($value)
    {

    }
}