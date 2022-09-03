<?php

namespace AJD_validation\Async;

use AJD_validation\Async\PromiseHelpers;
use AJD_validation\Async\Promise_interface;
use AJD_validation\Async\UnhandledRejectException;

class FailedPromise implements Promise_interface
{
    private $reason;
    private $promise;

    public function __construct($reason = null, $promise = null)
    {
        $this->reason = $reason;
        $this->promise = $promise;
    }

    public function getPromiseOrSelf()
    {
        return $this->promise ?? $this;
    }

    public function then(callable $onFulfilled = null, callable $onRejected = null)
    {
        if (null === $onRejected) 
        {
            return $this;
        }

        try 
        {
            return PromiseHelpers::resolve($onRejected($this->reason), $this->promise);
        } 
        catch (\Throwable $exception) 
        {
            return (new FailedPromise($exception, $this->promise))->getPromiseOrSelf();
        } 
        catch (\Exception $exception) 
        {
            return (new FailedPromise($exception, $this->promise))->getPromiseOrSelf();
        }
    }

    public function done(callable $onFulfilled = null, callable $onRejected = null)
    {
        if (null === $onRejected) 
        {
            $onRejected = function($reason)
            {
                return $reason;
            };
        }
        
        $result = $onRejected($this->reason);                

        if ($result instanceof self) 
        {
            throw UnhandledRejectException::resolve($result->reason);
        }

        if ($result instanceof Promise_interface) 
        {
            $result->done();
        }
    }

    public function otherwise(callable $onRejected)
    {
        /*if (!_checkTypehint($onRejected, $this->reason)) {
            return $this;
        }*/

        return $this->then(null, $onRejected);
    }

    public function always(callable $onFulfilledOrRejected)
    {
        $that = $this;
        return $this->then(null, function ($reason) use ($onFulfilledOrRejected, $that) 
        {
            return PromiseHelpers::resolve($onFulfilledOrRejected())->then(function () use ($reason, $that) 
            {
                return (new FailedPromise($reason, $that->promise))->getPromiseOrSelf();
            });
        });
    }

    public function progress(callable $onProgress)
    {
        return $this;
    }

    public function cancel()
    {
    }

    public function catch(callable $catch, $promise = null)
    {
        $promiseDefer = $this->promise;

        if(!empty($promise))
        {
            $promiseDefer = $promise;
        }

        return PromiseHelpers::catch($catch, $promiseDefer);
    }
}