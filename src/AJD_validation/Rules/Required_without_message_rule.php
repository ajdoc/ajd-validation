<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_dependent;

class Required_without_message_rule extends Abstract_dependent
{
	public $without 		= TRUE;
	public $showSubError 	= TRUE;
}