<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_dependent;

/**
 * Validates when all of the dependent field is present
 *
 * 
 */
class Required_with_all_rule extends Abstract_dependent
{
	public $any 	= FALSE;
}