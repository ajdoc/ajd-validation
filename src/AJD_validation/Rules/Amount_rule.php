<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_rule;
use AJD_validation\AJD_validation as v;

class Amount_rule extends Abstract_rule
{
	public $decimalPlace;

	public function __construct( $decimalPlace = null ) 
	{
		$this->decimalPlace = $decimalPlace;
	}

	public function run( $value, $satisfier = null, $field = null )
	{
		$value = $this->Famount()
					->cacheFilter('value')
					->filterSingleValue( $value, true );

		$check 	= false;

		if( !EMPTY( $satisfier ) )
		{
			$this->decimalPlace = ( is_array( $satisfier ) ) ? $satisfier[0] : $satisfier;
			$valueDec = strlen(substr(strrchr($value, "."), 1));
			$check = ( is_numeric( $value ) && $valueDec == $this->decimalPlace );

			if( $check )
			{
				$value = round($value, $this->decimalPlace, PHP_ROUND_HALF_UP);
			}
		}
		else
		{
			$check = is_numeric( $value );
		}

		return $check;
	}

	public function validate( $value )
	{
		$satisfier = [$this->decimalPlace];

		$check = $this->run( $value, $satisfier );

		if( is_array( $check ) )
		{
			return $check['check'];
		}

		return $check;
	}
}