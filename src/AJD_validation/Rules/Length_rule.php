<?php namespace AJD_validation\Rules;

use Exception;
use AJD_validation\Contracts\Abstract_rule;

class Length_rule extends Abstract_rule
{
	public $minValue;
	public $maxValue;
	public $inclusive;

	protected $validator;

	public function __construct($minValue = null, $maxValue = null, $inclusive = true)
	{
		$this->minValue = $minValue;
		$this->maxValue = $maxValue;
		$this->inclusive = $inclusive;
		$this->validator = $this->getValidator();
		$validator2 = $this->getValidator();
		$numeric = $validator2->numeric();
		$nullType = $validator2->null_type();
		$paramsValid = $this->validator->one_or( $numeric, $nullType );

		if( !$paramsValid->validate($minValue) )
		{
			throw new Exception(sprintf('%s is not a valid numeric length', $minValue));
		}

		if( !$paramsValid->validate($maxValue) )
		{
			throw new Exception(sprintf('%s is not a valid numeric length', $maxValue));
		}

		if( !is_null( $minValue ) && !is_null( $maxValue ) && $minValue > $maxValue )
		{
			throw new Exception(
				sprintf('%s cannot be less than %s for validation', $minValue, $maxValue)
			);
		}

	}

	public function run( $value, $satisfier = null, $field = null )
	{
		$check_arr = (is_array($value)) ? false : true;
		
		$check = false;
		$length = $this->Fextract_length()
					->cacheFilter('value')
					->filterSingleValue( $value, true, $check_arr );

		$check = ( $this->validateMin( $length ) && $this->validateMax($length) );

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

	protected function validateMin($length)
	{
		if( is_null( $this->minValue ) )
		{
			return true;
		}

		if( $this->inclusive )
		{
			return $length >= $this->minValue;
		}

		return $length > $this->minValue;
	}

	protected function validateMax($length)
	{
		if( is_null( $this->maxValue ) )
		{
			return true;
		}
		
		if( $this->inclusive )
		{
			return $length <= $this->maxValue;
		}

		return $length < $this->maxValue;
	}
}