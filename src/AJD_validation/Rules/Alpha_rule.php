<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_ctype;

class Alpha_rule extends Abstract_ctype
{
	protected function ctypeFunction($value)
    {
        return ctype_alpha($value);
    }

    protected function getRegexString()
    {
    	$addCharReg = $this->processAddtionalCharRegex();

    	if( !EMPTY( $addCharReg ) )
    	{
    		$regex = '/^[a-zA-Z'.$addCharReg.']+$/';
    	}
    	else
    	{
    		$regex = '/^[a-zA-Z]+$/';
    	}

    	return $regex;
    }
}