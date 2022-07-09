<?php namespace AJD_validation\Exceptions;
use AJD_validation\Contracts\Abstract_exceptions;

class Alpha_rule_exception extends Abstract_exceptions
{
	const EXTRA = 1;

	public static $defaultMessages 	= array(
        self::ERR_DEFAULT 			=> array(
            self::STANDARD 			=> ':field must contain only letters (a-z).',
            self::EXTRA 			=> ':field must contain only letters (a-z) and "{additionalChars}".',
        ),
        self::ERR_NEGATIVE 			=> array(
            self::STANDARD 			=> ':field must not contain letters (a-z).',
            self::EXTRA 			=> ':field must not contain letters (a-z) and "{additionalChars}".',
        ),
    );

    public static $localizeFile     = 'alpha_rule_err';   

    public function chooseMessage()
    {
        return $this->getParam('additionalChars') ? static::EXTRA : static::STANDARD;
    }
}