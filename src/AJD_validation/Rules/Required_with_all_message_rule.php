<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_dependent;

/**
 * Validates when all of the dependent field is present and will output sub error message
 *
 * 
 */
class Required_with_all_message_rule extends Abstract_dependent
{
	public $any = false;
	public $showSubError = true;
}