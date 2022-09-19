<?php

namespace AJD_validation\Traits;

use Closure;
use AJD_validation\Helpers\GivenProxy;

trait Conditionable
{
	/**
     * Indicates whether call proxy must be falsy.
     *
     * @var bool
     */
	protected $falsy = false;

	/**
     * Apply the callback if the given "value" is (or resolves to) truthy.
     *
     *
     * @param  mixed $value
     * @param  callable|null $callback
     * @param  callabel|null $default
     * @return $this|TWhenReturnType
     */
    public function given($value = null, callable $callback = null, callable $default = null)
    {
    	$this->falsy = false;

    	if(func_num_args() === 0) 
        {
        	return $this->callProxy();
        }

        if(func_num_args() === 1) 
        {
        	return $this->callProxy($value);
        }

        return $this->callProxy($value, $callback, $default);
    }

    /**
     * Apply the callback if the given "value" is (or resolves to) falsy.
     *
     *
     * @param  mixed $value
     * @param  callable|null $callback
     * @param  callabel|null $default
     * @return $this|TUnlessReturnType
     */
    public function otherwise($value = null, callable $callback = null, callable $default = null)
    {
    	$this->falsy = true;

    	if(func_num_args() === 0) 
        {
        	return $this->callProxy();
        }

        if(func_num_args() === 1) 
        {
        	return $this->callProxy($value);
        }

        return $this->callProxy($value, $callback, $default, true);
    }

    /**
     * Apply the callback if the given "value" is (or resolves to) falsy.
     *
     *
     * @param  mixed $value
     * @param  callable|null $callback
     * @param  callabel|null $default
     * @param  bool $falsy
     * @return $this|TUnlessReturnType|TWhenReturnType
     */
    protected function callProxy($value = null, callable $callback = null, callable $default = null, $falsy = false)
    {
    	$value = $value instanceof Closure ? $value($this) : $value;

    	$valCheck = ($falsy || $this->falsy) ? !$value : $value;

        if(func_num_args() === 0) 
        {
        	if($falsy || $this->falsy)
        	{
        		return (new GivenProxy($this))->negateConditionOnCapture();
        	}

            return new GivenProxy($this);
        }

        if(func_num_args() === 1) 
        {
            return (new GivenProxy($this))->condition($valCheck);
        }

        if($valCheck) 
        {
            return $callback($this, $value) ?? $this;
        } 
        elseif($default) 
        {
            return $default($this, $value) ?? $this;
        }

        return $this;
    }
}