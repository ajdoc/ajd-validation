<?php namespace AJD_validation\Exceptions;
use AJD_validation\Exceptions\Nested_rule_exception;

class None_rule_exception extends Nested_rule_exception
{
    public static $defaultMessages = [
        self::ERR_DEFAULT => [
            self::STANDARD => 'None of these rules must pass for :field.',
        ],
        self::ERR_NEGATIVE => [
            self::STANDARD => 'All of these rules must pass for :field.',
        ],
    );

    public static $localizeFile = 'none_rule_err';
}