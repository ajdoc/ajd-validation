<?php namespace AJD_validation\Contracts;

use AJD_validation\AJD_validation as v;
use AJD_validation\Contracts\Abstract_rule;

use DateTime;
use Exception;

abstract class Abstract_interval extends Abstract_rule
{
	public $inclusive;
    public $interval;

    public $isString = false;
    public $isNumeric;
 	
    public function __construct($inclusive = true, $isString = false)
    {
       $this->inclusive = $inclusive;
       $this->isString = $isString;
    }

    protected function filterInterval($value)
    {
        $this->interval = $value;

        $new_value = $this->Finterval()
                        ->cacheFilter( 'value' )
                        ->filterSingleValue( $value, true );
        
        return $new_value;
    }
}