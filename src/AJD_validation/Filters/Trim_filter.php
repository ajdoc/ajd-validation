<?php namespace AJD_validation\Filters;
use AJD_validation\Contracts\Abstract_filter;

class Trim_filter extends Abstract_filter
{
	public function filter( $value, $satisfier = NULL, $field = NULL )
	{
		$filtValue 		= $value;

        if( !EMPTY( $satisfier ) )
        {
            $filtValue  = trim( $value, $satisfier );
        }
        else
        {
            $filtValue  = trim( $value );
        }

        return $filtValue;
	}
}