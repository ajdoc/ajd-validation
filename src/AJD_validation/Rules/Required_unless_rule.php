<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_dependent;

/**
 * Validates when one of the dependent field is present and equals to dependent the value
 *
 * 
 */
class Required_unless_rule extends Abstract_dependent
{
	public $any = false;
	public $needsComparing = true;
}