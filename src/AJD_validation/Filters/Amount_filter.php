<?php namespace AJD_validation\Filters;
use AJD_validation\Contracts\Abstract_filter;

class Amount_filter extends Abstract_filter
{
	public function filter( $value, $satisfier = NULL, $field = NULL )
	{
		$filtValue 		= $value;

        if( !EMPTY( $satisfier ) )
        {
            $filtValue  = trim( str_replace(',', '', $value ), $satisfier );
        }
        else
        {
            $filtValue  = trim( str_replace(',', '', $value ) );
        }

        return $filtValue;
	}
}