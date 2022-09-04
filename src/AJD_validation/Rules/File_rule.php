<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_rule;

class File_rule extends Abstract_rule
{
	public function run( $value, $satisfier = null, $field = null )
	{
		if( $value instanceof \SplFileInfo )
		{
			$check = $value->isFile();
		}
		else
		{
			$check = ( is_string( $value ) && is_file( $value ) );
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