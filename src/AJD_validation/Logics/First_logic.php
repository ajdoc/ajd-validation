<?php namespace AJD_validation\Logics;

use AJD_validation\Contracts\Abstract_logic;

class First_logic extends Abstract_logic
{
	public function __construct( $validator )
	{
		// print_r(func_get_args());
	}

	public function logic( $value )
	{
		return TRUE;
	}
}