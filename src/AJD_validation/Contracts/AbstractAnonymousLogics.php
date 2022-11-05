<?php namespace AJD_validation\Contracts;

use AJD_validation\Contracts\Abstract_logic;

abstract class AbstractAnonymousLogics extends Abstract_logic
{
    protected $extraArgs = [];

    abstract public function __invoke($value, $parameters = null);

    public function setExtraArgs(array $extraArgs)
    {
        $this->extraArgs = $extraArgs;
    }

    public function getExtraArgs()
    {
        return $this->extraArgs;
    }

    public function logic( $value )
    {
        return false;
    }
}