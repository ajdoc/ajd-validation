<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_dependent;

/**
 * Validates when one of the dependent field is not present
 *
 * 
 */
class Required_without_rule extends Abstract_dependent
{
	public $without 		= TRUE;
}