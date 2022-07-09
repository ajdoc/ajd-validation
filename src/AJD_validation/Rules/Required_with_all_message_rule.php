<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_dependent;

class Required_with_all_message_rule extends Abstract_dependent
{
	public $any 			= FALSE;
	public $showSubError 	= TRUE;
}