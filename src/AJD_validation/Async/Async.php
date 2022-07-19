<?php

namespace AJD_validation\Async;

use \Fiber;
use AJD_validation\Async\PromiseValidator;
use AJD_validation\AJD_validation;
	
class Async
{
	public static $activeAwaits = [];
	public static $fails 		= [];
	public static $passes 		= [];

	protected $currentThenFiber;
	protected $errorMessages = [];
	protected $whenFibers = [];

	public static function await(PromiseValidator $childFiber)
	{
		self::$activeAwaits[] = [
			null, $childFiber->getFiber()
		];

		static::$passes = [];
		static::$fails = [];

		return $childFiber;
	}

	public static function when()
	{
		$args = func_get_args();

		$childFibers = [];

		$self = new static;;

		if(!empty($args))
		{
			foreach($args as $childFiber)
			{
				static::await($childFiber);

				$childFibers[] = $childFiber;
			}
		}

		$self->whenFibers = $childFibers;

		return $self;
	}

	public function promise(callable $function = null, mixed ...$args)
	{
		$func = $function;

		if(empty($func))
		{
			$func = function(){};
		}

		return $this->run($func, $this->whenFibers, $args);
	}

	public static function awaitAndStart(Fiber $childFiber)
	{
		self::$activeAwaits[] = [
			Fiber::getCurrent(), $childFiber
		];

		$childFiber->start();

		while ($childFiber->isTerminated() === false)
		{
			$childFiber->resume();

			// Don't suspend here if the childFiber is now terminated - it's
			// a wasted suspension.

			if(!$childFiber->isTerminated())
			{
				Fiber::suspend();
			}
			else
			{
				break;
			}
		}

		$returnValue = $childFiber->getReturn();

		return $returnValue;
	}

	public static function run(callable $resolver, array $childFibers = [], mixed ...$args)
	{
		$self = new static;
		$childFibers = [];

		$childFibersToRun = self::$activeAwaits;

		$errorMessages = [];

		if(!empty($childFibers))
		{
			$childFibersToRun = $childFibers;
		}
		
		while (count($childFibersToRun) > 0)
		{
			$toRemove = [];

			foreach($childFibersToRun as $index => $pair)
			{
				$parentFiber  = $pair[0] ?? null;
				$childFiber   = $pair[1];

				$childFibers[$index] = $childFiber;
				
				if($parentFiber)
				{
					if ($parentFiber->isSuspended() && $parentFiber->isTerminated() === false)
					{

						// Resume the parent fiber
						$parentFiber->resume();
					}
					elseif ($parentFiber->isTerminated())
					{
						// Register this fiber index to be removed from the activeAwaits
						$toRemove[] = $index;
					}
					else
					{
						$toRemove[] = $index;
					}
				}
				else if(!$parentFiber && $childFiber)
				{
					if(!$childFiber->isStarted())
					{
						$childFiber->start();
					}

					if ($childFiber->isSuspended() && $childFiber->isTerminated() === false)
					{

						// Resume the parent fiber
						$childFiber->resume();
					}
					elseif ($childFiber->isTerminated())
					{
						$returnValue = $childFiber->getReturn();

						$returnValue->fails(function($ajd, $field) use(&$self)
						{

							static::$fails[] = 1;

							if( $ajd->validation_fails($field) )
							{

								if(
									!empty($ajd->errors()->outputError(true, $field))
								)
								{
									$errorMsg = $ajd->errors()->toStringErr($ajd->errors()->find($field));
									
									$self->errorMessages[] = [
										'errorMessages' => $errorMsg,
										'field' => $field,
										'ajd' => $ajd
									];
								}
							}
						})
						->passed(function()
						{
							static::$passes[] = 1;	
						});

						// Register this fiber index to be removed from the activeAwaits
						$toRemove[] = $index;
					}
					else
					{
						$toRemove[] = $index;
					}
				}
				else
				{
					$toRemove[] = $index;
				}
			}

			foreach($toRemove as $indexToRemove)
			{
				if(!empty($childFibers))
				{
					unset($childFibersToRun[$indexToRemove]);
					unset($self->whenFibers[$indexToRemove]);
					
				}
				
				unset(self::$activeAwaits[$indexToRemove]);	
				
				
			}

			// Re-index the array
			if(!empty($childFibers))
			{
				$childFibersToRun 		= array_values($childFibersToRun);	
				$self->whenFibers 		= array_values($self->whenFibers);	
			}
			
			self::$activeAwaits = array_values(self::$activeAwaits);	
			
			
		}
		
		return $self->async($resolver, $self->errorMessages, $self, $childFibers, $self->errorMessages, static::$passes, static::$fails, ...$args);
	}

	public static function async(callable $function, array $errorMessages = [], $self, mixed ...$args)
	{

		return (static function (mixed ...$args) use ($function, $errorMessages, $self) 
		{
			
			$fiber = null;

			$promise = new PromiseValidator(function(callable $resolve, callable $reject, $target) use ($function, $args, $errorMessages, &$fiber)
			{
				$ajd 			= AJD_validation::get_ajd_instance();
				$obs            = $ajd::get_observable_instance(false);
				
				$obs->attach_observer( 'passed', $target, array( $ajd ) );
				$obs->attach_observer( 'fails', $target, array( $ajd ) );

				$fiber = new Fiber(function () use ($resolve, $reject, $function, $args, $errorMessages, &$fiber, &$obs)
				{
					
					try 
					{
						if(!empty(static::$fails) && in_array(1, static::$fails))
						{
							throw new \Exception('Promise fails');
						}	

						$obs->notify_observer( 'passed' );
	                    $resolve($function(...$args));
	                } 
	                catch (\Throwable $exception) 
	                {
	                	$obs->notify_observer( 'fails' );
	                    $reject($exception);
	                } 
	                finally 
	                {

	                }
	            });

				$target->setFiber($fiber);
			   	$fiber->start();
			}, 
			function(callable $resolve, callable $reject, $target) use (&$fiber)
			{
				$ajd 			= AJD_validation::get_ajd_instance();
				$obs            = $ajd::get_observable_instance(false);

				$obs->attach_observer( 'fails', $target, array( $ajd ) );

				$obs->notify_observer( 'fails' );

				if (\method_exists($target, 'cancel')) 
				{
                	$target->cancel();
                }
            },
				$errorMessages,
			);

			/*$lowLevelFiber = Fiber::getCurrent();

	        if ($lowLevelFiber !== null) 
	        {
	            FibersCollection::setPromise($lowLevelFiber, $promise);
	        }*/

		 	return $promise;
		})(...$args);

	}
}
