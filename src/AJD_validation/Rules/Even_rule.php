<?php namespace AJD_validation\Rules;

use AJD_Validation\Contracts\Abstract_rule;

class Even_rule extends Abstract_rule
{
	public function run( $value, $satisfier = NULL, $field = NULL )
	{
		if( !is_numeric( $value ) )
		{
			$check 	= FALSE;	
		}
		else
		{
			$check 	= ((int) $value % 2 === 0);
		}

		return $check;
	}

	public function validate( $value )
	{
		$check              = $this->run( $value );

        if( is_array( $check ) )
        {
            return $check['check'];
        }

        return $check;
	}
}