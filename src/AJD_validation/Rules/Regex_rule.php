<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_regex;

class Regex_rule extends Abstract_regex
{
	public $regex;

	public function __construct($regex)
	{
		$this->regex 	= $regex;
		// parent::__construct($regex);
	}

	protected function getRegex()
    {
    	return 	'/'.$this->regex.'/';
    }
}