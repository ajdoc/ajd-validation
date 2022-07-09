<?php namespace AJD_validation\Filters;
use AJD_validation\Contracts\Abstract_filter;

class Extract_length_filter extends Abstract_filter
{
	public function filter( $value, $satisfier = NULL, $field = NULL )
	{
		if( is_string( $value ) )
		{
			return mb_strlen( $value, mb_detect_encoding( $value ) );
		}

		if( is_array( $value ) AND $value instanceof \Countable )
		{
			return count( $value );
		}

		if( is_object( $value ) )
		{
			return count( get_object_vars( $value ) );
		}

		if( is_int( $value ) )
		{
			return strlen( (string) $value );
		}

		return FALSE;
	}
}