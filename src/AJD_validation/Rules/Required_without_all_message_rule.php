<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_dependent;

/**
 * Validates when all of the dependent field is not present and will output sub error message
 *
 * 
 */
class Required_without_all_message_rule extends Abstract_dependent
{
	public $without 		= TRUE;
	public $any 			= FALSE;
	public $showSubError 	= TRUE;
}