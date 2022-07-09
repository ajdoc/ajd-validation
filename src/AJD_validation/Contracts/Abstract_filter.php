<?php namespace AJD_validation\Contracts;

use AJD_validation\Contracts\Filter_interface;
use AJD_validation\AJD_validation;

abstract class Abstract_filter extends AJD_validation implements Filter_interface
{
	public function __invoke($value, $satisfier = NULL, $field = NULL)
    {
        return $this->filter($value, $satisfier, $field);
    }
}