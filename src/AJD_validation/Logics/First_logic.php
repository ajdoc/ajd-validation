<?php namespace AJD_validation\Logics;

use AJD_validation\Contracts\Abstract_logic;

class First_logic extends Abstract_logic
{
	protected $test = false;
	public function __construct( $test = false, $validator = null )
	{
		$this->test = $test;
	}

	public function logic( $value )
	{
		return $this->test;
	}
}