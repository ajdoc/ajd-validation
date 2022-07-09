<?php namespace AJD_validation\Filters;
use AJD_validation\Contracts\Abstract_filter_sanitize;

class Filter_sanitize_filter extends Abstract_filter_sanitize
{
	public function __construct()
	{
		$arguments 			= func_get_args();

		$this->arguments 	= $arguments;

		parent::__construct();
	}
}