<?php namespace AJD_validation\Observer;

use \Closure;

use AJD_validation\Factory\Factory_strategy;

class Observable
{
	protected $observers = [];
	protected $observer_args = [];

	protected $observerCustomEvent = [];

	protected static $statObserver = [];
	protected static $statObserverArgs = [];
	protected static $statObserverCustomEvent = [];
	protected static $factory;

	protected static function get_factory_instance()
	{
		if( !static::$factory instanceof Factory_strategy ) 
		{
			static::$factory = new Factory_strategy();
		}

		return static::$factory;
	}

	public function attach_observer( $event, $observer, $obs_args = null, $customEvent = null )
	{
		$this->observers[ $event ][] = $observer;

		if( !empty( $obs_args ) ) 
		{
			$this->observer_args[ $event ][] = $obs_args;
		}

		if( !empty( $customEvent ) ) 
		{
			$this->observerCustomEvent[$event] = $customEvent;
		}

	}

	public static function attach_static_observer( $event, $observer, $obs_args = null, $customEvent = null )
	{
		static::$statObserver[ $event ][] = $observer;

		if( !empty( $obs_args ) ) 
		{
			static::$statObserverArgs[ $event ][] = $obs_args;
		}

		if( !empty( $customEvent ) ) 
		{
			static::$statObserverCustomEvent[$event] = $customEvent;
		}
	}

	public function notify_static_observer($event, array $args = [], $return_specific = false)
	{
		return $this->notify_common_observer($event, static::$statObserver, static::$statObserverArgs, $args, $return_specific, static::$statObserverCustomEvent);
	}

	public function notify_observer($event, array $args = [], $return_specific = false)
	{
		return $this->notify_common_observer($event, $this->observers, $this->observer_args, $args, $return_specific, $this->observerCustomEvent);
	}

	public function notify_common_observer( $event, array $observers, array $observer_args, array $extra_args = [], $return_specific = false, array $customEvent = [] )
	{
		$method_factory = static::get_factory_instance()->get_instance( false, false, true );
		$function_factory = static::get_factory_instance()->get_instance( false, true, false );

		if( isset( $observers[ $event ] ) ) 
		{
			$cnt = 0;
			
			foreach( $observers[ $event ] as $observer ) 
			{
				$args = $this->process_args( $event, $cnt, $observer, $observer_args );
				$args = array_merge( $args, $extra_args );

				$eventArgs = $this->process_args( $event, $cnt, $observer, $observer_args, true );
				$eventArgs = array_merge( $eventArgs, $extra_args );
				
				$realArgs = [];

				if(class_exists($event))
				{
					$eventObject = new $event;

					$customEventMethod = $customEvent[$event] ?? 'handle';

					if(method_exists($eventObject, $customEventMethod))
					{
						$eventObject->{$customEventMethod}($eventArgs);
					}

					$realArgs[] = $eventObject;
				}

				$realArgs = array_merge($realArgs, $args);
				$realArgs = array_merge($realArgs, $extra_args);

				if( $observer instanceof Closure && is_callable( $observer ) ) 
				{
					$function = $function_factory->rules( $observer );
					
					$function->invokeArgs( $realArgs );				
				} 
				else 
				{
					if( is_array( $observer ) ) 
					{
						if( count( $observer ) > 2 ) throw new \InvalidArgumentException(' array must have 2 elements only. ( object, method ) ');

						$method = $method_factory->rules( $observer[0], $observer[1] );

						if( $method->isProtected() || $method->isPublic() ) 
						{
							$method->setAccessible( true );

							$invoked = $method->invokeArgs( $observer[0], $realArgs );
						}
					} 
					else 
					{
						$method = $method_factory->rules( $observer, 'triggerEvent' );

						if($return_specific)
						{
							return [
								'method' => $method,
								'observer' => $observer,
								'args' => $realArgs
							];
						}
						else
						{
							$method->invokeArgs( $observer, $realArgs );
						}
					}
				}

				$cnt++;
			}
		}
	}

	protected function process_args( $event, $cnt, $observer, array $observer_args, $no_default = false )
	{
		$args = [];

		if(!$no_default)
		{
			$args[] = $event;
			$args[] = $observer;
		}

		if( $this->_check_args( $observer_args, $event ) ) 
		{
			if( $this->_check_args( $observer_args[ $event ], $cnt ) ) 
			{
				$arr = $observer_args[ $event ][ $cnt ];

				foreach ( $arr as $key => $value ) 
				{
					$args[] = $value;
				}
			}
		}

		return $args;
	}

	private function _check_args( array $arr, $key )
	{
		$check = ( isset( $arr[ $key ] ) AND !empty( $arr[ $key ] ) );

		return $check;

	}

	public function detach_observer( $event )
	{
		if( isset( $this->observers[ $event ] ) ) 
		{
			unset( $this->observers[ $event ] );
		}

		if( isset( $this->observer_args[ $event ] ) ) 
		{
			unset( $this->observer_args[ $event ] );
		}

		if( isset( $this->observerCustomEvent[ $event ] ) ) 
		{
			unset( $this->observerCustomEvent[ $event ] );
		}

		if( isset( static::$statObserver[ $event ] ) ) 
		{
			unset( static::$statObserver[ $event ] );
		}

		if( isset( static::$statObserverArgs[ $event ] ) ) 
		{
			unset( static::$statObserverArgs[ $event ] );
		}

		if( isset( static::$statObserverCustomEvent[ $event ] ) ) 
		{
			unset( static::$statObserverCustomEvent[ $event ] );
		}
	}

}


