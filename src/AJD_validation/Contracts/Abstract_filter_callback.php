<?php namespace AJD_validation\Contracts;

use AJD_validation\Contracts\Abstract_filter;

abstract class Abstract_filter_callback extends Abstract_filter
{
	public $callback;
    public $arguments 		= array();

    public function __construct($callback)
    {
    	if( !is_callable( $callback ) )
    	{
    		throw new Exception('Invalid callback.');
    	}

    	$this->callback 	= $callback;

    }

    public function filter( $value, $satisfier = NULL, $field = NULL )
    {
    	$args 				= $this->arguments;
    	array_unshift($args, $value);
    	
    	$filtValue 			= call_user_func_array($this->callback, $args);
    	
    	return $filtValue;
    }
}