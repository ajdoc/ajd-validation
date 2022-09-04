<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_rule;

class No_whitespace_rule extends Abstract_rule
{
	public function run($value, $satisfier = null, $field = null)
    {
        $check = false;

    	if ( is_null( $value ) ) 
        {
            $check = true;
        }

        if (false === is_scalar( $value ) ) 
        {
            $check = false;
        }

        $check = !preg_match('#\s#', $value);

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