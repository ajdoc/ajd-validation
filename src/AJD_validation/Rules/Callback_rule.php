<?php namespace AJD_validation\Rules;
use AJD_validation\Contracts\Abstract_callback;

class Callback_rule extends Abstract_callback
{
	public function __construct( $callback )
	{
		$arguments 	= func_get_args();

		array_shift( $arguments );

		$this->arguments = $arguments;

		parent::__construct( $callback );
	}
}