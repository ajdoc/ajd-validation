<?php namespace AJD_validation\Contracts;

use AJD_validation\Contracts\Logic_interface;
use AJD_validation\AJD_validation;

abstract class Abstract_logic implements Logic_interface
{
	public function __invoke($value)
    {
    	$this->logic($value);
    }
}