<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_invokable;

class Fiberize_rule extends Abstract_invokable
{
    public function __construct()
    {
    }

	public function __invoke($value, $satisfier = NULL, $field = NULL)
    {
        return true;
    }
}

