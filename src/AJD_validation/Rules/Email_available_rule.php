<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_database;

class Email_available_rule extends Abstract_database
{
	protected $queryConfig		= array(
		parent::TABLE_STR		=> 'users',
		parent::PRIMARY_ID_STR	=> 'email'
	);

	protected $reverseCheck 	= TRUE;
}

