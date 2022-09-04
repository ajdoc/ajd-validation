<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_ctype;

class Alnum_rule extends Abstract_ctype
{
	protected function ctypeFunction($value)
    {
        return ctype_alnum($value);
    }

    protected function getRegexString()
    {
    	$addCharReg = $this->processAddtionalCharRegex();

    	if( !EMPTY( $addCharReg ) )
    	{
    		$regex = '/^[a-zA-Z0-9'.$addCharReg.']+$/';
    	}
    	else
    	{
    		$regex = '/^[a-zA-Z0-9]+$/';
    	}

    	return $regex;
    }
}