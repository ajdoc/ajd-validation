<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_regex;

class Vowel_rule extends Abstract_regex
{
	protected function getRegex()
    {
       return '/^(\s|[aeiouAEIOU])*$/';
    }
}