<?php namespace AJD_validation\Filters;
use AJD_validation\Contracts\Abstract_filter;

class Size_count_filter extends Abstract_filter
{
	public function filter( $value, $satisfier = null, $field = null )
	{
		$filtValue = $value;

		if( is_numeric($value) ) 
		{
            $filtValue = $value;
        } 
        else if( is_array($value) ) 
        {
            $filtValue = count($value);
        }
        else if( is_object( $value ) )
        {
        	$value = (array) $value;
        	$filtValue = count( $value );
        }
        else
        {
        	$filtValue = mb_strlen( $value );
        }

        return $filtValue;
	}
}