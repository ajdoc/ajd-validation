<?php 

namespace AJD_validation\Async;

use AJD_validation\Async\PromiseHelpers;
use AJD_validation\Async\FailedPromise;
use AJD_validation\Async\Promise_interface;

class Promise implements Promise_interface
{
	protected $cancel;
    protected $result;

    protected $handlers = [];
    protected $progressHandlers = [];

    protected $requiredCancelPromise = 0;
    protected $cancelPromise = 0;

    protected $errors;

	public function __construct(callable $resolver, callable $cancel = null)
    {
        $this->cancel = $cancel;
        $cb = $resolver;
        $resolver = null;
        $cancel = null;
        $this->call($cb);

        if(!empty($this->errors))
        {
            PromiseHelpers::setHasErrors($this->errors);
        }
        
    }

    public function setHasErrors(array $errors)
    {
        if(!empty($errors))
        {
            PromiseHelpers::setHasErrors($errors);
        }
    }

    public function then(callable $onFulfilled = null, callable $onRejected = null)
    {

        if (null !== $this->result) 
        {
            return $this->result->then($onFulfilled, $onRejected);
        }

        if (null === $this->cancel) 
        {
            return new static($this->resolver($onFulfilled, $onRejected));
        }

        $parent = $this;
        ++$parent->requiredCancelPromise;

        return new static(
            $this->resolver($onFulfilled, $onRejected),
            static function () use (&$parent) 
            {
                if (++$parent->cancelPromise >= $parent->requiredCancelPromise ) 
                {
                    $parent->cancel();
                }

                $parent = null;
            }
        );
    }

    public function done(callable $onFulfilled = null, callable $onRejected = null)
    {
        if (null !== $this->result) 
        {
            return $this->result->done($onFulfilled, $onRejected);
        }

        $this->handlers[] = static function ($promise) use ($onFulfilled, $onRejected) 
        {
            $promise
                ->done($onFulfilled, $onRejected);
        };
    }

    public function otherwise(callable $onRejected)
    {
        return $this->then(null, static function ($reason) use ($onRejected) 
        {
            /*if (!_checkTypehint($onRejected, $reason)) 
            {
                return new FailedPromise($reason);
            }*/

            return $onRejected($reason);
        });
    }

    public function always(callable $onFulfilledOrRejected)
    {
        $that = $this;
        return $this->then(static function ($value) use ($onFulfilledOrRejected, $that) 
        {
            return PromiseHelpers::resolve($onFulfilledOrRejected())->then(function () use ($value, $that) 
            {
                return $value;
            });
        }, 
        static function ($reason) use ($onFulfilledOrRejected, $that) 
        {
            return PromiseHelpers::resolve($onFulfilledOrRejected())->then(function () use ($reason, $that) 
            {
                return (new FailedPromise($reason, $that))->getPromiseOrSelf();
            });
        });
    }

    /*public function progress(callable $onProgress)
    {
        return $this->then(null, null, $onProgress);
    }*/

    private function resolver(callable $onFulfilled = null, callable $onRejected = null)
    {
        $that = $this;
        return function ($resolve, $reject, $notify) use ($onFulfilled, $onRejected, $that) 
        {
            /*if ($onProgress) 
            {
            	$progressHandler = static function ($update) use ($notify, $onProgress) {
                    try 
                    {
                        $notify($onProgress($update));
                    } 
                    catch (\Throwable $e) 
                    {
                        $notify($e);
                    } 
                    catch (\Exception $e) 
                    {
                        $notify($e);
                    }
                };
            } 
            else 
            {*/
                $progressHandler = $notify;
            // }
            $this->handlers[] = static function ($promise) use ($onFulfilled, $onRejected, $resolve, $reject, $progressHandler, $that) 
            {
                $promise
                    ->then($onFulfilled, $onRejected)
                    ->done($resolve, $reject);
                    
            };

            $this->progressHandlers[] = $progressHandler;
        };
    }

    private function reject($reason = null)
    {
        if (null !== $this->result) 
        {
            return;
        }

        $this->settle(PromiseHelpers::reject($reason));
    }

    private function settle($promise)
    {
        $promise = $this->unwrap($promise);

        if ($promise === $this) 
        {
            $promise = new RejectedPromise(
                new \LogicException('Cannot resolve a promise with itself.')
            );
        }

        $handlers = $this->handlers;

        $this->progressHandlers = $this->handlers = [];
        $this->result = $promise;
        $this->cancel = null;

        foreach ($handlers as $handler) 
        {
            $handler($promise);
        }
    }

    public function cancel()
    {
        if (null === $this->cancel || null !== $this->result) 
        {
            return;
        }

        $cancel = $this->cancel;
        $this->cancel = null;

        $this->call($cancel);
    }

    private function unwrap($promise)
    {
        $promise = $this->extract($promise);

        while ($promise instanceof self && null !== $promise->result) 
        {
            $promise = $this->extract($promise->result);
        }

        return $promise;
    }

    private function extract($promise)
    {

        return $promise;
    }

    private function call(callable $cb)
    {
        $callback = $cb;
        $cb = null;

        
        if (\is_array($callback)) 
        {
            $ref = new \ReflectionMethod($callback[0], $callback[1]);
        } 
        elseif (\is_object($callback) && !$callback instanceof \Closure) 
        {
            $ref = new \ReflectionMethod($callback, '__invoke');
        } 
        else 
        {
            $ref = new \ReflectionFunction($callback);
        }

        $args = $ref->getNumberOfParameters();

        try 
        {
            if ($args === 0) 
            {
                $callback();
            } 
            else 
            {
                $target =& $this;
                $progressHandlers =& $this->progressHandlers;

                $callback(
                    static function ($value = null) use (&$target) 
                    {
                        if ($target !== null) 
                        {
                            $target->settle(PromiseHelpers::resolve($value));
                            $target = null;
                        }
                    },
                    static function ($reason = null) use (&$target) 
                    {
                        if ($target !== null) 
                        {
                            $target->reject($reason);
                            $target = null;
                        }
                    },
                    $target
                );
            }
        } 
        catch (\Throwable $e) 
        {
            $target = null;
            $this->reject($e);
        } 
        catch (\Exception $e) 
        {
            $target = null;
            $this->reject($e);
        }
    }

}