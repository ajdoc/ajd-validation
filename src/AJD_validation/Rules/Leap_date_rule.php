<?php namespace AJD_validation\Rules;

use DateTime;

use AJD_validation\Contracts\Abstract_rule;

class Leap_date_rule extends Abstract_rule
{
	protected $format 	= 'Y-m-d';

	public function __construct( $format = 'Y-m-d' )
	{
		$this->format 	= $format;
	}

	public function run( $value, $satisfier = NULL, $field = NULL )
	{
		$check 		= FALSE;
		$date 		= NULL;

		if( !EMPTY( $satisfier ) )
		{
			if( is_array( $satisfier ) )
			{
				$this->format = $satisfier[0];
			}
			else
			{
				$this->format = $satisfier;
			}
		}

		if( is_string( $value ) )
		{
			$date 	= DateTime::createFromFormat($this->format, $value);
		}
		else if( $value instanceof DateTime )   
		{
			$date 	= $value;
		}
		else
		{
			$check 	= FALSE;
		}

		if( !EMPTY( $date ) )
		{
			$check 		= $date->format('m-d') == '02-29';
		}

		return $check;
	}

	public function validate( $value )
	{
		$satisfier 		= array( $this->format );

		$check 			= $this->run( $value, $satisfier );

		if( is_array( $check ) )
		{
			return $check['check'];
		}

		return $check;
	}
}