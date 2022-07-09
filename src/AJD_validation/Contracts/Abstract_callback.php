<?php namespace AJD_validation\Contracts;
use AJD_validation\Contracts\Abstract_rule;

abstract class Abstract_callback extends Abstract_rule
{
	public $callback;
    public $arguments 		= array();

    public function __construct($callback)
    {
        if( !is_callable( $callback ) )
        {
            throw new Exception('Invalid callback.');
        }

        $this->callback     = $callback;
    }

    public function run( $value, $satisfier = NULL, $field = NULL )
    {
    	$args 				= $this->arguments;
    	array_unshift($args, $value);
    	
    	$check 				= call_user_func_array($this->callback, $args);

    	return $check;
    }

    public function validate( $value )
    {
    	$check              = $this->run( $value );

        if( is_array( $check ) )
        {
            return $check['check'];
        }

        return $check;
    }
}