<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_dependent;

class Required_without_all_message_rule extends Abstract_dependent
{
	public $without 		= TRUE;
	public $any 			= FALSE;
	public $showSubError 	= TRUE;
}