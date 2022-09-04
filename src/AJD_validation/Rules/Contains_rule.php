<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_search;

class Contains_rule extends Abstract_search
{
	public $reverse = true;
	public $haystack;
	public $identical;

	public function __construct( $haystack = null, $identical = null )
	{
		$this->haystack = $haystack;
		$this->identical = $identical;
	}
}