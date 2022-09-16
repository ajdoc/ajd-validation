<?php namespace AJD_validation\Exceptions;
use AJD_validation\Exceptions\Nested_rule_exception;

class Attribute_rule_exception extends Nested_rule_exception
{
    const NOT_PRESENT = 0;
    const INVALID = 1;

	public static $defaultMessages = [
        self::ERR_DEFAULT => [
            self::NOT_PRESENT => 'Attribute :field must be present.',
            self::INVALID => 'Attribute :field must be valid.'
        ],
        self::ERR_NEGATIVE => [
            self::NOT_PRESENT => 'Attribute :field must not be present.',
            self::INVALID => 'Attribute :field must not be valid.'
        ],
    ];

    public static $localizeFile = 'attribute_rule_err';

    public function chooseMessage()
    {
       return $this->getParam('hasReference') ? static::INVALID : static::NOT_PRESENT;
    }
}