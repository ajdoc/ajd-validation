<?php namespace AJD_validation\Filters;
use AJD_validation\Contracts\Abstract_filter;

class Std_to_array_filter extends Abstract_filter
{
	public function filter( $value, $satisfier = null, $field = null )
	{
		$filtValue = ( array ) $value;

        return $filtValue;
	}
}