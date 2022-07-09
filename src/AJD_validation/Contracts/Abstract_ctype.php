<?php namespace AJD_validation\Contracts;

use AJD_validation\Contracts\Abstract_filter_type;
use AJD_validation\AJD_validation as v;

abstract class Abstract_ctype extends Abstract_filter_type
{
	abstract protected function ctypeFunction($value);

	protected function getRegexString()
    {
        return '';
    }

	public function validateValue($value)
    {
        return $this->ctypeFunction($value);
    }
}