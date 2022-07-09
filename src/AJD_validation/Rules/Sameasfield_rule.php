<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_rule;

class Sameasfield_rule extends Abstract_rule
{

	public function run( $value, $satisfier = NULL, $field = NULL )
	{

		$check 		= TRUE;
		
		if( ISSET( $satisfier[1][ $satisfier[0] ] ) )
		{
			$same 	= $satisfier[1][ $satisfier[0] ];

			$check 	= $value == $same;

		}
		

		return $check;
		
	}

	public function validate( $value )
	{
		return true;
	}
}