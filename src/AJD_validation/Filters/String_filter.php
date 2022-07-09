<?php namespace AJD_validation\Filters;
use AJD_validation\Contracts\Abstract_filter_sanitize;

class String_filter extends Abstract_filter_sanitize
{
	public function __construct()
	{
		parent::__construct(FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
	}
}