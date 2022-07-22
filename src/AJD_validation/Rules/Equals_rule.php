<?php namespace AJD_validation\Rules;
use AJD_validation\Contracts\Abstract_rule;

class Equals_rule extends Abstract_rule
{
	public $compareto;

	public function __construct($compareto)
	{
		$this->compareto 	= $compareto;
	}

	public function run( $value, $satisfier = NULL, $field = NULL )
	{
		$check 		= $value == $this->compareto;
		
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