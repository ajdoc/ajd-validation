<?php namespace AJD_validation\Rules;
use AJD_validation\Contracts\Abstract_callback;

class Null_type_rule extends Abstract_callback
{
	public function __construct()
	{
		parent::__construct('is_null');
	}
}