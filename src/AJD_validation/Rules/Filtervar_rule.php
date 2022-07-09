<?php namespace AJD_validation\Rules;
use AJD_validation\Contracts\Abstract_filter_validate;

class Filtervar_rule extends Abstract_filter_validate
{
	public function __construct()
	{
		$arguments 			= func_get_args();

		$this->arguments 	= $arguments;

		parent::__construct();
	}
}