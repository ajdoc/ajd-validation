<?php namespace AJD_validation\Contracts;

use \Exception;
use AJD_validation\Contracts\Abstract_filter_callback;

abstract class Abstract_filter_sanitize extends Abstract_filter_callback
{
	protected $validFilters 	= array(
	 	FILTER_SANITIZE_FULL_SPECIAL_CHARS,
	 	FILTER_SANITIZE_NUMBER_INT,
        FILTER_SANITIZE_NUMBER_FLOAT,
        FILTER_SANITIZE_EMAIL,
        FILTER_SANITIZE_URL,
        FILTER_FLAG_ALLOW_FRACTION,
        FILTER_FLAG_NO_ENCODE_QUOTES
	);

	public function __construct()
    {
        $args       = func_get_args();
        
        if( !EMPTY( $args ) )
        {
            $this->arguments    = $args;
        }
        
        if( !ISSET( $this->arguments[0] ) ) 
        {
            throw new Exception('Cannot sanitize without filter flag');
        }

        if( !$this->isValidFilter( $this->arguments[0] ) ) 
        {
            throw new Exception('Cannot accept the given filter');
        }

        $this->callback         = 'filter_var';
    }

	private function isValidFilter($filter)
    {
    	return in_array(
    		$filter,
    		$this->validFilters
    	);
    }
}

