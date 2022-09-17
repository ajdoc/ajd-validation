<?php namespace AJD_validation\Contracts;

use AJD_validation\Contracts\Abstract_filter_type;
use AJD_validation\AJD_validation as v;

abstract class Abstract_regex extends Abstract_filter_type
{
	abstract protected function getRegex();

	protected function getRegexString()
    {
        return '';
    }

	public function validateValue($value)
    {
        return preg_match($this->getRegex(), $value);
    }
}