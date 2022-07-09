<?php namespace AJD_validation\Filters;
use AJD_validation\Contracts\Abstract_filter_sanitize;

class Float_filter extends Abstract_filter_sanitize
{
	public function __construct()
	{
		parent::__construct(FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
	}
}