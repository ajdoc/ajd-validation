<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_regex;

class Mobileno_rule extends Abstract_regex
{
	public $startNo 	= FALSE;

	public function __construct($additionalChars = '', $startNo = FALSE)
	{
		parent::__construct($additionalChars);

		$this->startNo 	= $startNo;
	}

	protected function getRegex()
    {
    	if( $this->startNo )
    	{
    		return '/^(\+639)\d{9}$/';
    	}
    	else
    	{
			return '/^(9|09)\d{9}$/';
    	}
  
    }
}