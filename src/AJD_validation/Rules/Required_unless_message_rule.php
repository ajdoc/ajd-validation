<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_dependent;

/**
 * Validates when all of the dependent field is present and equals to dependent the value and will output sub error message
 *
 * 
 */
class Required_unless_message_rule extends Abstract_dependent
{
	public $any = false;
	public $needsComparing = true;
	public $showSubError = true;
}