<?php namespace AJD_validation\Rules;

use DateTime;
use AJD_validation\Contracts\Abstract_rule;

class Url_rule extends Abstract_rule
{
	const VERY_BASIC = 'verybasic';

	public $schemes = [];
	public $removeVeryBasic;

	public function __construct($schemes = null)
	{
		$this->schemes = $schemes;

		if( !EMPTY( $schemes ) )
		{
			if( in_array(self::VERY_BASIC, $schemes) )
			{
				$this->removeVeryBasic = true;
			}
		}
	}

	public function run( $value, $satisfier = null, $field = null )
	{
		$checkArr = [];

		if( empty( $this->schemes ) )
		{
			$check = $this->validateCommonScheme( $value );
		}
		else
		{
			foreach( $this->schemes as $scheme )
			{
				$method = 'validate' . ucfirst($scheme) .'Scheme';

				if( method_exists( $this, $method ) ) 
				{
					if( $this->{$method}($value) ) 
					{
						$checkArr[] = true;
					}
				}
				else if( $this->validateCommonScheme( $value, $scheme ) ) 
				{
					$checkArr[] = true;
				}
			}

			$check = false;
		}

		if( !empty( $checkArr ) )
		{
			$check = !in_array( false, $checkArr );
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


    protected function validateCommonScheme($value, $scheme = null)
    {
    	$check = ( $this->validateBasic( $value ) && (bool) preg_match("/^\w+:\/\//i", $value) );

    	if( !empty( $scheme ) ) 
    	{
    		$check = ( $this->validateBasic($value) && (bool) preg_match("/^{$scheme}:\/\//", $value) );
    	}

    	return $check;
    }

 	protected function validateBasic($value)
    {
    	$check = filter_var($value, FILTER_VALIDATE_URL) !== false;

        return $check;
    }

	protected function validateMailtoScheme($value)
	{
		$check = ( $this->validateBasic($value) && preg_match("/^mailto:/", $value) );

		return $check;
	}

	protected function validateVerybasicScheme($value)
	{
		$pattern = '/(?:https?:\/\/)?(?:[a-zA-Z0-9.-]+?\.(?:com|net|org|gov|edu|mil)|\d+\.\d+\.\d+\.\d+)/';

		$check = (bool) preg_match($pattern, $value);

		return $check;
	}

	protected function validateJdbcScheme($value)
	{
		$check = ( (bool) preg_match("/^jdbc:\w+:\/\//", $value) );

		return $check;
	}
}