<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_ctype;

class Digit_rule extends Abstract_ctype
{
	protected function ctypeFunction($value)
    {
        return ctype_digit($value);
    }
}