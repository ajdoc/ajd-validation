<?php

namespace AJD_validation\Async;

use AJD_validation\Async\PromiseValue;
use AJD_validation\Async\PromiseValidator;
use AJD_validation\Async\FailedPromise;
use AJD_validation\Async\PromiseCollection;
use AJD_validation\Async\Promise;
use AJD_validation\Async\Promise_interface;

class PromiseHelpers
{
    protected static $errors;

    public function __construct()
    {
    }

    public static function setHasErrors(array $errors)
    {
        static::$errors = $errors;    
        
    }

    public static function resolve($promiseOrValue = null, $promise = null)
    {
       if (\is_object($promiseOrValue) && \method_exists($promiseOrValue, 'then')) 
        {
            $canceller = null;
            if (\method_exists($promiseOrValue, 'cancel')) 
            {
                $canceller = [$promiseOrValue, 'cancel'];
            }
            
            return new PromiseValidator(function ($resolve, $reject) use ($promiseOrValue) 
            {
                $promiseOrValue->then($resolve, $reject);
            });
        }
        
        if($promise)
        {
            return $promise->setValue($promiseOrValue);
        }
        else
        {
            return new PromiseValue($promiseOrValue);
        }
    }

    public static function reject($promiseOrValue = null, $promise = null)
    {
        if ($promiseOrValue instanceof Promise_interface) 
        {
            return static::resolve($promiseOrValue)->then(function ($value) 
            {
                return (new FailedPromise($value))->getPromiseOrSelf();
            });
        }

        return (new FailedPromise($promiseOrValue))->getPromiseOrSelf();
    }

    public static function catch(callable $catch, $promise = null)
    {
        if(!empty(static::$errors))
        {
            try
            {
                $errorMessages = '';

                foreach(static::$errors as $e)
                {
                    $ajd = $e['ajd'];
                    $field = $e['field'];

                    $ajdMessage = $ajd->getPropMessage();

                    unset($ajdMessage[$field]);

                    $ajd->setPropMessage($ajdMessage);

                    $errorMessages .= $e['errorMessages'];
                }

                if(!empty($errorMessages))
                {
                    static::$errors = [];
                    throw new \Exception($errorMessages);   
                }
            }
            catch(\Exception $exception)
            {
                return PromiseHelpers::resolve($catch($exception));
            }
        }

        if(!empty($promise))
        {
            return $promise->setValue();
        }
        else
        {
            return new PromiseValue();    
        }
        
    }

    public static function all($promisesOrValues)
    {
        return static::map($promisesOrValues, function($val) 
        {
            return $val;
        });
    }

    public static function any($promisesOrValues)
    {
        return static::some($promisesOrValues, 1)
            ->then(function ($val) 
            {
                return \array_shift($val);
            });
    }

    public static function map($promisesOrValues, callable $func)
    {
        $promiseCollection = new PromiseCollection();
        $promiseCollection->collect($promisesOrValues);

        return new PromiseValidator(function ($resolve, $reject, $target) use ($promisesOrValues, $func, $promiseCollection) 
        {
            PromiseHelpers::resolve($promisesOrValues)
                ->done(function ($array) use ($func, $promiseCollection, $resolve, $reject, $target) 
                {
                    if (!\is_array($array) || !$array) 
                    {
                        $resolve([]);
                        return;
                    }
                    
                    $toResolve = \count($array);
                    $values = [];

                    foreach ($array as $i => $promiseOrValue) 
                    {
                        $promiseCollection->collect($promiseOrValue);
                        $values[$i] = null;

                        PromiseHelpers::resolve($promiseOrValue)
                            ->then($func)
                            ->done(
                                function ($mapped) use ($i, &$values, &$toResolve, $resolve) 
                                {
                                    $values[$i] = $mapped;

                                    if (0 === --$toResolve) 
                                    {
                                        $resolve($values);
                                    }
                                },
                                $reject
                            );
                    }
                }, $reject);
        }, $promiseCollection);
    }


    public static function some($promisesOrValues, $number)
    {
        $promiseCollection = new PromiseCollection();
        $promiseCollection->collect($promisesOrValues);

        return new PromiseValidator(function ($resolve, $reject, $target) use ($promisesOrValues, $number, $promiseCollection) 
        {
            PromiseHelpers::resolve($promisesOrValues)
                ->done(function ($array) use ($number, $promiseCollection, $resolve, $reject, $target) 
                {
                    if (!\is_array($array) || $number < 1) 
                    {
                        $resolve([]);
                        return;
                    }

                    $len = \count($array);

                    if ($len < $number) {
                        throw new \LengthException(
                            \sprintf(
                                'Input array must contain at least %d item%s but contains only %s item%s.',
                                $number,
                                1 === $number ? '' : 's',
                                $len,
                                1 === $len ? '' : 's'
                            )
                        );
                    }

                    $toResolve = $number;
                    $toReject = ($len - $toResolve) + 1;
                    $values = [];
                    $reasons = [];

                    foreach ($array as $i => $promiseOrValue) 
                    {
                        $onFulfilled = function ($val) use ($i, &$values, &$toResolve, $toReject, $resolve) 
                        {
                            if ($toResolve < 1 || $toReject < 1) 
                            {
                                return;
                            }

                            $values[$i] = $val;

                            if (0 === --$toResolve) 
                            {
                                $resolve($values);
                            }
                        };

                        $onRejected = function ($reason) use ($i, &$reasons, &$toReject, $toResolve, $reject) 
                        {
                            if ($toResolve < 1 || $toReject < 1) 
                            {
                                return;
                            }

                            $reasons[$i] = $reason;

                            if (0 === --$toReject) 
                            {
                                $reject($reasons);
                            }
                        };

                        $promiseCollection->collect($promiseOrValue);

                        PromiseHelpers::resolve($promiseOrValue)
                            ->done($onFulfilled, $onRejected);
                    }
                }, $reject);
        }, $promiseCollection);
    }
}