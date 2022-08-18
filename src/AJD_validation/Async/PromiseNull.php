<?php 

namespace AJD_validation\Async;

class PromiseNull implements Promise_interface
{
	public function then(callable $onFulfilled = null, callable $onRejected = null)
    {
    	return null;
    }

   	public function done(callable $onFulfilled = null, callable $onRejected = null)
    {
    	return null;
    }

    public function catch(callable $catch)
    {
    	return null;
    }

    public function otherwise(callable $onRejected)
    {
    	return null;
    }

    public function always(callable $onFulfilledOrRejected)
    {
    	return null;
    }
}