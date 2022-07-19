<?php

namespace AJD_validation\Async;

use AJD_validation\Async\PromiseHelpers;
use AJD_validation\Async\FailedPromise;
use AJD_validation\Async\Promise_interface;

class PromiseValue implements Promise_interface
{

    private $value;

    public function __construct($value = null)
    {
        $this->value = $value;
    }

    public function setValue($value = null)
    {
        $this->value = $value;

        return $this;
    }

    public function then(callable $onFulfilled = null, callable $onRejected = null)
    {
        if (null === $onFulfilled) 
        {
            return $this;
        }

        try 
        {
            return PromiseHelpers::resolve($onFulfilled($this->value));
        } 
        catch (\Throwable $exception) 
        {
            return (new FailedPromise($exception))->getPromiseOrSelf();
        } 
        catch (\Exception $exception) 
        {
            return (new FailedPromise($exception))->getPromiseOrSelf();
        }
    }

    public function done(callable $onFulfilled = null, callable $onRejected = null, callable $onProgress = null)
    {
        if (null === $onFulfilled) 
        {
            return;
        }

        $result = $onFulfilled($this->value);

        if ($result instanceof Promise_interface) {
            $result->done();
        }
    }

    public function otherwise(callable $onRejected)
    {
        return $this;
    }

    public function always(callable $onFulfilledOrRejected)
    {
        return $this->then(function ($value) use ($onFulfilledOrRejected) 
        {
            return PromiseHelpers::resolve($onFulfilledOrRejected())->then(function () use ($value) 
            {
                return $value;
            });
        });
    }

    public function catch(callable $catch)
    {
        return PromiseHelpers::catch($catch);
    }
}