<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_dependent;

class Required_if_rule extends Abstract_dependent
{
	public $needsComparing 	= TRUE;
}