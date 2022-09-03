<?php namespace AJD_validation\Contracts;

use \Exception;
use AJD_validation\Contracts\Abstract_callback;

abstract class Abstract_filter_validate extends Abstract_callback
{
	protected $validFilters = [
	 	FILTER_VALIDATE_BOOLEAN,
        FILTER_VALIDATE_EMAIL,
        FILTER_VALIDATE_FLOAT,
        FILTER_VALIDATE_INT,
        FILTER_VALIDATE_IP,
        FILTER_VALIDATE_REGEXP,
        FILTER_VALIDATE_URL,
	];

	public function __construct()
    {
        $args = func_get_args();

        if( !EMPTY( $args ) )
        {
            $this->arguments = $args;
        }
        
        if( !ISSET( $this->arguments[0] ) ) 
        {
            throw new Exception('Cannot validate without filter flag');
        }

        if( !$this->isValidFilter( $this->arguments[0] ) ) 
        {
            throw new Exception('Cannot accept the given filter');
        }

        $this->callback = 'filter_var';
    }

	private function isValidFilter($filter)
    {
    	return in_array(
    		$filter,
    		$this->validFilters
    	);
    }
}

