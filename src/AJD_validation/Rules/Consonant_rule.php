<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_regex;

class Consonant_rule extends Abstract_regex
{
	protected function getRegex()
    {
       return '/^(\s|[b-df-hj-np-tv-zB-DF-HJ-NP-TV-Z])*$/';
    }
}