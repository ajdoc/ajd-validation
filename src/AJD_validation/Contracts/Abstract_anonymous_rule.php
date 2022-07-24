<?php namespace AJD_validation\Contracts;

use AJD_validation\AJD_validation as v;
use AJD_validation\Contracts\Abstract_rule;
use AJD_validation\Contracts\Abstract_anonymous_rule_exception;

abstract class Abstract_anonymous_rule extends Abstract_rule
{
	abstract public static function getAnonName();

    abstract public static function getAnonExceptionClass();
}