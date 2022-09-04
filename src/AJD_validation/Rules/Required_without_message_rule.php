<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_dependent;

/**
 * Validates when one of the dependent field is not present and will output sub error message
 *
 * 
 */
class Required_without_message_rule extends Abstract_dependent
{
	public $without = true;
	public $showSubError = true;
}