<?php 

namespace AJD_validation\Traits;

use AJD_validation\AJD_validation;
use \Closure;

Trait Events_dispatcher_trait 
{
	public static $PASSED 	= 'passed';
	public static $FAILS 	= 'fails';
	public static $FIBER 	= 'fiber';

	protected static $valid_events 	= [ ];

	protected $customEvents 		= array();

	protected $event;
	protected $ajd;
	protected $observer;
	protected $customObserver;

	protected $field;
	protected $fibers = [];
	protected $eventField;

	public function __construct()
	{
		static::$valid_events = [
			static::$PASSED, static::$FAILS, static::$FAILS
		];
	}

	public function triggerEvent( $event, $observer, $ajd, $fibers = null )
	{
		$eventArr 	= explode('-|', $event);
		
		if(isset($eventArr[1]))
		{
			$event = $eventArr[1];
		}
		else
		{
			$event 	= $eventArr[0];
		}
		
		$this->fibers[$event] = $fibers;
		$this->event 		= strtolower( $event );
		$this->ajd 			= $ajd;
		$this->observer 	= $observer;
	}

	public function passed( Closure $func )
	{
		if( $this->check_event( static::$PASSED ) ) 
		{
			$this->invoke_function( $func );
		}

		return $this;
	}

	public function fails( Closure $func )
	{
		if( $this->check_event( static::$FAILS ) ) 
		{
			$this->invoke_function( $func );
		}

		return $this;
	}

	public function fiber( Closure $func )
	{
		if( isset($this->fibers[static::$FIBER]) && !empty($this->fibers[static::$FIBER]) ) 
		{
			foreach($this->fibers[static::$FIBER] as $field => $rules)
			{
				foreach($rules as $rule => $value)
				{
					$paramaters_sub = [];

					$fiber = $value;

					$paramaters_sub[] = $this->ajd;
					$paramaters_sub[] = $fiber['fiber'];
					$paramaters_sub[] = $field;
					$paramaters_sub[] = $rule;
					$paramaters_sub[] = $fiber['fiber_suspend_val'];
					
					/*$this->ajd->addFiberEvents($func, $this->ajd, $fiber['fiber'], $fiber['paramaters'], $fiber['fiber_suspend_val'], $rule, $field);*/
					call_user_func_array($func, $paramaters_sub);

				}
			}

			// $this->invoke_function( $func, self::FIBER );
		}
		
		return $this;
	}

	public function publish($event)
	{
		if( !EMPTY( $this->customEvents ) )
		{
			$obs 	= $this->customEvents['observer'];

			$obs->notify_observer($event);

			// $this->resetCustomEvent();
		}

		return $this;
	}

	public function publishSuccess($event, $field = NULL)
	{
		return $this->publishSuccessFail($event, FALSE, $field);
	}

	public function publishFail($event, $field = NULL)
	{
		return $this->publishSuccessFail($event, TRUE, $field);
	}

	protected function publishSuccessFail($event, $fails = FALSE, $chainField = NULL)
	{
		if( !EMPTY( $this->customEvents ) )
		{
			$obs 	= $this->customEvents['observer'];
			$ajd 	= $this->customEvents['ajd'];

			if( !EMPTY( $chainField ) )
			{
				$field 	= $chainField;
			}
			else
			{
				$field 	= $this->customEvents['field'];
			}
			
			if( !$fails )
			{
				if( !$ajd->validation_fails($field) ) 
				{
					$obs->notify_observer( $event );
					// $this->resetCustomEvent();
				}
			}
			else
			{
				if( $ajd->validation_fails($field) ) 
				{
					$obs->notify_observer( $event );
					// $this->resetCustomEvent();
				}
				
			}
		}

		return $this;
	}

	protected function resetCustomEvent()
	{
		$this->customEvents 	= array();
	}

	public function customEvent($event, $eventDispatcher, $observer, $ajd, $field)
	{
		$this->customEvents 	= array(
			'field' 	=> $field,
			'event'		=> $event,
			'eventDispatcher'	=> $eventDispatcher,
			'observer' 	=> $observer,
			'ajd' 		=> $ajd
		); 
	}

	protected function check_event( $event )
	{
		$check 	= ( in_array( $this->event, static::$valid_events ) AND $this->event == $event );

		return $check;

	}

	protected function invoke_function( $func, $args = [], $event = NULL )
	{
		$paramaters = [
			$this->ajd
		];
		
		if(!empty($this->fibers) && !empty($event))
		{
			if(isset($this->fibers[$event]) && !empty($this->fibers[$event]))
			{
				$paramaters[] = $this->fibers[$event]['fiber'];	
				$paramaters[] = $this->fibers[$event]['paramaters'];	
			}
			
		}

		call_user_func_array( $func, $paramaters );

		$this->fibers = [];
	}
}