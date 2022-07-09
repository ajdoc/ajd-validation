<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_regex;

class Phone_rule extends Abstract_regex
{
    protected function getRegex()
    {
        return '/^[+]?([\d]{0,3})?[\(\.\-\s]?(([\d]{1,3})[\)\.\-\s]*)?(([\d]{3,5})[\.\-\s]?([\d]{4})|([\d]{2}[\.\-\s]?){4})$/';
    }
}