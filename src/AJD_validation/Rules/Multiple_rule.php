<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_rule;

class Multiple_rule extends Abstract_rule
{
	public $multipleof;

	public function __construct($multipleof)
	{
		if( !is_numeric( $multipleof ) )
		{
			throw new \Exception('Invalid Multiplier.');
		}

		$this->multipleof = $multipleof;
	}

	public function run( $value, $satisfier = null, $field = null )
	{
		$check = false;

		if( $this->multipleof == 0 )
		{
			$check = ( $value == 0 );
		}
		else
		{
			if( is_numeric( $value ) )
			{
				$check = ( $value % $this->multipleof == 0 );
			}
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