<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_search;

class In_rule extends Abstract_search
{
	public $haystack;
	public $identical;

	public function __construct( $haystack = null, $identical = null )
	{
		$this->haystack = $haystack;
		$this->identical = $identical;
	}
}