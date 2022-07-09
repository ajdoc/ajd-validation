<?php namespace AJD_validation\Observer;

use \Closure;

use AJD_validation\Factory\Factory_strategy;

class Observable
{
	protected $observers 				= array();
	protected $observer_args 			= array();
	protected static $statObserver 		= array();
	protected static $statObserverArgs 	= array();
	protected static $factory;

	protected static function get_factory_instance()
	{
		if( !static::$factory instanceof Factory_strategy ) 
		{
			static::$factory 	= new Factory_strategy();
		}

		return static::$factory;
	}

	public function attach_observer( $event, $observer, $obs_args = NULL )
	{
		$this->observers[ $event ][] 	 		= $observer;

		if( !EMPTY( $obs_args ) ) 
		{
			$this->observer_args[ $event ][] 	= $obs_args;
		}

	}

	public static function attach_static_observer( $event, $observer, $obs_args = NULL )
	{
		static::$statObserver[ $event ][] 	 		= $observer;

		if( !EMPTY( $obs_args ) ) 
		{
			static::$statObserverArgs[ $event ][] 	= $obs_args;
		}

	}

	public function notify_static_observer($event, array $args = array())
	{
		$this->notify_common_observer($event, static::$statObserver, static::$statObserverArgs, $args);
	}

	public function notify_observer($event, array $args = array())
	{
		$this->notify_common_observer($event, $this->observers, $this->observer_args, $args);
	}

	public function notify_common_observer( $event, array $observers, array $observer_args, array $extra_args = array() )
	{
		$method_factory 					= static::get_factory_instance()->get_instance( FALSE, FALSE, TRUE );
		$function_factory 	 				= static::get_factory_instance()->get_instance( FALSE, TRUE, FALSE );

		if( ISSET( $observers[ $event ] ) ) 
		{
			$cnt 							= 0;
			
			foreach( $observers[ $event ] as $observer ) 
			{
				$args 						= $this->process_args( $event, $cnt, $observer, $observer_args );
				$args 						= array_merge( $args, $extra_args );
				// print_r($extra_args);
				if( $observer instanceof Closure AND is_callable( $observer ) ) 
				{
					$function 				= $function_factory->rules( $observer );
					
					$function->invokeArgs( $args );					
				} 
				else 
				{
					if( is_array( $observer ) ) 
					{
						if( count( $observer ) > 2 ) throw new \InvalidArgumentException(' array must have 2 elements only. ( object, method ) ');

						$method 			= $method_factory->rules( $observer[0], $observer[1] );

						if( $method->isProtected() OR $method->isPublic() ) 
						{
							$method->setAccessible( TRUE );

							$method->invokeArgs( $observer[0], $args );
						}
					} 
					else 
					{
						$method 			= $method_factory->rules( $observer, 'trigger' );

						$method->invokeArgs( $observer, $args );
					}

				}

				$cnt++;

			}

		}

	}

	protected function process_args( $event, $cnt, $observer, array $observer_args )
	{
		$args 				= array();

		$args[] 			= $event;
		$args[] 			= $observer;

		if( $this->_check_args( $observer_args, $event ) ) 
		{
			if( $this->_check_args( $observer_args[ $event ], $cnt ) ) 
			{
				$arr 		= $observer_args[ $event ][ $cnt ];

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
		$check 		= ( ISSET( $arr[ $key ] ) AND !EMPTY( $arr[ $key ] ) );

		return $check;

	}

	public function detach_observer( $event )
	{
		if( ISSET( $this->observers[ $event ] ) ) 
		{
			unset( $this->observers[ $event ] );
		}

	}

}


