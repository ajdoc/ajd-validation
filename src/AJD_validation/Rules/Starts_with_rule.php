<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_rule;

class Starts_with_rule extends Abstract_rule
{
	public $startValue;
	public $identical;

	public function __construct($startValue, $identical = false)
	{
		$this->startValue = $startValue;
		$this->identical = $identical;
	}

	public function run( $value, $satisfier = null, $field = null )
	{
		$check = false;

		if( $this->identical )
		{
			$check = $this->validateIdentical( $value );
		}
		else
		{
			$check = $this->validateEquals( $value );
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

	protected function validateIdentical( $value )
	{
		if( is_array( $value ) )
		{
			return reset( $value ) === $this->startValue;
		}

		return 0 === mb_strpos( $value, $this->startValue, 0, mb_detect_encoding( $value ) );
	}

	protected function validateEquals( $value )
	{
		if( is_array( $value ) )
		{
			return reset( $value ) == $this->startValue;
		}

		return 0 === mb_stripos( $value, $this->startValue, 0, mb_detect_encoding( $value ) );
	}
}