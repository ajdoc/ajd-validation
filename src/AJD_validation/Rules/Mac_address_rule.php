<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_regex;

class Mac_address_rule extends Abstract_regex
{
	protected function getRegex()
    {
    	return '/^(([0-9a-fA-F]{2}-){5}|([0-9a-fA-F]{2}:){5})[0-9a-fA-F]{2}$/';
    }
}