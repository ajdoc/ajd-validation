<?php namespace AJD_validation\Rules;

use DateTime;

use AJD_validation\Contracts\Abstract_rule;

class Leap_year_rule extends Abstract_rule
{
	public function run( $value, $satisfier = NULL, $field = NULL )
	{
		$check 		= FALSE;
		$check_date = TRUE;

		if( is_numeric( $value ) )
		{
			$value 	= ( int ) $value;
		}
		else if( is_string( $value ) )
		{
			$value 	= ( int ) date( 'Y', strtotime( $value ) );
		}
		else if( $value instanceof DateTime )   
		{
			$value 	= ( int ) $value->format('Y');
		}
		else
		{
			$check 	= FALSE;
			$check_date = FALSE;
		}

		if( $check_date )
		{
			$date 		= strtotime( sprintf('%d-02-29', $value ) );

			$check 		= ( bool ) date('L', $date);
		}

		return $check;
	}

	public function validate( $value )
	{
		$check 		= $this->run( $value );

		if( is_array( $check ) )
		{
			return $check['check'];
		}

		return $check;
	}
}