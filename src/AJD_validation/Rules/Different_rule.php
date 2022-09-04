<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_rule;

class Different_rule extends Abstract_rule
{
	public $compareTo;
	public $identical;

	public function __construct($compareTo, $identical = false)
	{
		$this->compareTo = $compareTo;
		$this->identical = $identical;
	}

	public function run( $value, $satisfier = null, $field = null )
	{
		$check = $this->compareTo != $value;

		if( $this->identical )
		{
			$check = $this->compareTo !== $value;
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