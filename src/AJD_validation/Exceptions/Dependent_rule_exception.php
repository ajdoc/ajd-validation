<?php namespace AJD_validation\Exceptions;
use AJD_validation\Exceptions\Nested_rule_exception;

class Dependent_rule_exception extends Nested_rule_exception
{
	const NEEDS_COMPARING 			= 2;

    public static $defaultMessages 	= array(
        self::ERR_DEFAULT 			=> array(
            self::STANDARD 			=> ':field is validated when either {fieldsDisplay} passes all the required rules.',
            self::NEEDS_COMPARING 	=> ':field is validated when either {fieldsDisplay} passes all the required rules and is in {valueDisplay}.',
        ),
        self::ERR_NEGATIVE 			=> array(
         self::STANDARD             => ':field is not validated when either {fieldsDisplay} passes all the required rules.',
          self::NEEDS_COMPARING 	=> ':field is not validated when either {fieldsDisplay} passes all the required rules and is in {valueDisplay}.',
        ),
    );

    public static $localizeFile     = 'dependent_rule_err';

    public function chooseMessage()
    {
       return $this->getParam('needsComparing') ? static::NEEDS_COMPARING : static::STANDARD;
    }
}