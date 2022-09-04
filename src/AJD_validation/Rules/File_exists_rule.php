<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_rule;

class File_exists_rule extends Abstract_rule
{
	public function run( $value, $satisfier = null, $field = null )
	{
		if( $value instanceof \SplFileInfo )
		{
			$value = $value->getPathname();
		}
		
		$check = ( is_string( $value ) && file_exists( $value ) );
		

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