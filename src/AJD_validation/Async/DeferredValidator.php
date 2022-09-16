<?php

namespace AJD_validation\Async;

use AJD_validation\Async\{
    Promise_interface, PromiseValidator
};

class DeferredValidator implements Promise_interface
{
    private $promise;
    private $resolveCallback;
    private $rejectCallback;
    private $canceller;

    public function __construct(callable $canceller = null)
    {
        $this->canceller = $canceller;
    }

    public function promise()
    {
        if (null === $this->promise) 
        {
            $this->promise = new PromiseValidator(function ($resolve, $reject) 
            {
                $this->resolveCallback = $resolve;
                $this->rejectCallback = $reject;
            }, $this->canceller);

            $this->canceller = null;
        }

        return $this->promise;
    }

    public function resolve($value = null)
    {
        $this->promise();

        \call_user_func($this->resolveCallback, $value);
    }

    public function reject($reason = null)
    {
        $this->promise();

        \call_user_func($this->rejectCallback, $reason);
    }
}