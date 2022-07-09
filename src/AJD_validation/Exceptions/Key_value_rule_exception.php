<?php namespace AJD_validation\Exceptions;
use AJD_validation\Exceptions\Nested_rule_exception;

class Key_value_rule_exception extends Nested_rule_exception
{
    const COMPONENT = 1;

	public static $defaultMessages 	= array(
        self::ERR_DEFAULT 			=> array(
            self::STANDARD 		    => 'Key :field must be present.',
            self::COMPONENT         => '{formatBaseKey} must be valid to validate {formatComparekey}.'
        ),
        self::ERR_NEGATIVE 			=> array(
            self::STANDARD          => 'Key :field must not be present.',
            self::COMPONENT         => '{formatBaseKey} must not be valid to validate {formatComparekey}.'
        ),
    );

    public static $localizeFile     = 'key_value_rule_err';

    public function chooseMessage()
    {
        return $this->getParam('component') ? static::COMPONENT : static::STANDARD;
    }
}