<?php namespace AJD_validation\Rules;

use Exception;
use AJD_validation\Contracts\Abstract_rule;

class Digit_count_rule extends Abstract_rule
{
	public $digitLength;

	public function __construct( $digitLength )
	{
		if( !is_numeric( $digitLength ) )
		{
			throw new Exception('Digit length must be numeric.');
		}

		$digitLength = $this->Fnumeric()
						->cacheFilter('value')
						->filterSingleValue( $digitLength, true );

		$this->digitLength = $digitLength;
	}
	
	public function run( $value, $satisfier = null, $field = null )
	{
		if(!$this->getValidator()->digit()->validate($value))
		{
			return false;
		}

		$digitCount = strlen( preg_replace( '/[^0-9]+/', "", $value ) );
		$check = true;

		if( !empty( $this->digitLength ) )
		{
			$check = $digitCount == $this->digitLength;
		}

		return $check;
	}

	public function validate( $value )
	{
	 	$check = $this->run( $value );

        if( is_array( $check ) )
        {
            return $check['check'];
        }

        return $check;
	}

}