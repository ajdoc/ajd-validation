<?php namespace AJD_validation\Rules;

use Exception;
use AJD_validation\Contracts\Abstract_rule;

class Size_rule extends Abstract_rule
{
	public $size;

	public $isNumeric;
	public $isString;
	public $isArray;
	public $isFile;

	public function __construct($size)
	{
		$size = $this->Fnumeric()
                ->cacheFilter( 'value' )
                ->filterSingleValue( $size, true );

        if( !is_numeric( $size ) )	                      
        {
        	throw new Exception('Size must be numeric.');
        }

		$this->size = $size;
	}

	public function run( $value, $satisfier = null, $field = null )
	{
		if( is_numeric( $value ) )
		{
			$this->isNumeric = true;
		}
		else if( is_array( $value ) || is_object( $value ) )
		{
			$this->isArray = true;
		}
		else
		{
			$this->isString = true;
		}

		$check_arr = is_array($value) ? false : true;

		$countVal = $this->Fsize_count()
                    ->cacheFilter( 'value' )
                    ->filterSingleValue( $value, true, $check_arr );

	    $check = $countVal == $this->size;

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