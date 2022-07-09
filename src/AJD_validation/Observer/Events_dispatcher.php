<?php namespace AJD_validation\Observer;

use AJD_validation\Observer\Observable;
use \Closure;

class Events_dispatcher 
{
	const PASSED 	= 'passed';
	const FAILS 	= 'fails';

	protected static $valid_events 	= [ self::PASSED, self::FAILS ];

	protected $customEvents 		= array();

	protected $event;
	protected $ajd;
	protected $observer;
	protected $customObserver;

	protected $field;

	public function trigger( $event, $observer, $ajd )
	{
		$this->event 		= strtolower( $event );
		$this->ajd 			= $ajd;
		$this->observer 	= $observer;
	}

	public function passed( Closure $func )
	{
		if( $this->check_event( self::PASSED ) ) 
		{
			$this->invoke_function( $func );
		}

		return $this;
	}

	public function fails( Closure $func )
	{
		if( $this->check_event( self::FAILS ) ) 
		{
			$this->invoke_function( $func );
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

	protected function invoke_function( Closure $func )
	{
		call_user_func_array( $func, array( $this->ajd ) );
	}

}

