<?php namespace AJD_validation\Filters;
use AJD_validation\Contracts\Abstract_filter;

class Add_aes_decrypt_filter extends Abstract_filter
{
	public function filter( $value, $satisfier = NULL, $field = NULL )
	{
		$filtValue 		= $value;

        $filtValue      = 'AES_DECRYPT('.$value.', UNHEX(SHA2("'.$satisfier.'", 512)))';

        return $filtValue;
	}
}