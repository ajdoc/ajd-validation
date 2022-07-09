<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_rule;

class No_whitespace_rule extends Abstract_rule
{
	public function run($value, $satisfier = NULL, $field = NULL)
    {
        $check      = FALSE;

    	if ( IS_NULL( $value ) ) 
        {
            $check  = TRUE;
        }

        if (FALSE === is_scalar( $value ) ) 
        {
            $check  = FALSE;
        }

        $check      = !preg_match('#\s#', $value);

        return $check;
    }

    public function validate( $value )
    {
        $check      = $this->run( $value );

        if( is_array( $check ) )
        {
            return $check['check'];
        }

        return $check;
    }
}