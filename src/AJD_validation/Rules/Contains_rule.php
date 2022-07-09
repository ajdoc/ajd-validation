<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_search;

class Contains_rule extends Abstract_search
{
	public $reverse 	= TRUE;
	public $haystack;
	public $identical;

	public function __construct( $haystack = NULL, $identical = NULL )
	{
		$this->haystack 	= $haystack;
		$this->identical 	= $identical;
	}
}