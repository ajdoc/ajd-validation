<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_rule;

class Required_allowed_zero_rule extends Abstract_rule
{
	public function run( $value, $satisfier = null, $field = null )
	{   
		$check = false;

		if( is_numeric( $value ) ) 
		{
            $check = $value != 0;
        }

        if( is_string( $value ) ) 
        {
            if( $value === '0' )
            {
                settype($value, 'int');
            }
            else
            {
                $value = $this->Ftrim()
                            ->cacheFilter('value')
            			 ->filterSingleValue( $value, true );
            }
        }

        if ($value instanceof stdClass) 
        {
            $value = $this->Fstd_to_array()
			         ->cacheFilter('value')
        			 ->filterSingleValue( $value, true );
        }
        
        $check = ( !is_null( $value ) && $value != '' );

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

