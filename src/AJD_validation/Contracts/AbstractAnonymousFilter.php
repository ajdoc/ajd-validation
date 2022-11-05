<?php namespace AJD_validation\Contracts;

use AJD_validation\Contracts\Abstract_filter;

abstract class AbstractAnonymousFilter extends Abstract_filter
{
    protected $extraArgs = [];

    public function setExtraArgs(array $extraArgs)
    {
        $this->extraArgs = $extraArgs;
    }

    public function getExtraArgs()
    {
        return $this->extraArgs;
    }

    public function filter($value, $satisfier = null, $field = null)
    {
        return null;
    }
}