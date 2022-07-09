<?php namespace AJD_validation\Filters;

use AJD_validation\Contracts\Abstract_filter;

class Test_filter extends Abstract_filter
{

	public function filter( $value, $satisfier = NULL, $field = NULL )

	{

		return $value.'_custom_class_filter';
	}

}