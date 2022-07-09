<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_rule;

class Ends_with_rule extends Abstract_rule
{
	public $endValue;
	public $identical;

	public function __construct( $endValue, $identical = FALSE )
	{
		$this->endValue 	= $endValue;
		$this->identical 	= $identical;
	}

	public function run( $value, $satisfier = NULL, $field = NULL )
	{
		$check 		= FALSE;

		if( $this->identical )
		{
			$check 	= $this->validateIdentical( $value );
		}
		else
		{
			$check 	= $this->validateEquals( $value );
		}

		return $check;
	}

	public function validate( $value )
	{
		$check 		= $this->run( $value );

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
			return end( $value ) === $this->endValue;
		}

		return mb_strpos( $value, $this->endValue, 0, $enc = mb_detect_encoding( $value ) ) === mb_strlen( $value, $enc ) - mb_strlen( $this->endValue, $enc );
	}

	protected function validateEquals( $value )
	{
		if( is_array( $value ) )
		{
			return end( $value ) == $this->endValue;
		}

		return mb_strripos( $value, $this->endValue, 0, $enc = mb_detect_encoding( $value ) ) === mb_strlen( $value, $enc ) - mb_strlen( $this->endValue, $enc );
	}
}