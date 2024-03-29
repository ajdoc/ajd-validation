<?php namespace AJD_validation\Rules;

use DateTime;
use AJD_validation\Contracts\Abstract_interval;
use AJD_validation\Vefja\Vefja;

class Age_rule extends Abstract_interval
{
	protected $value;
	protected $field;
	public $minAge;
    public $maxAge;

    public function __construct( $minAge = null, $maxAge = null, $inclusive = true )
    {
    	$this->minAge = $minAge;
    	$this->maxAge = $maxAge;
    	$this->inclusive = $inclusive;
    }  

	public function run( $value, $satisfier = null, $field = null )
	{
		$this->value = $value;
		$this->field = $field;

		if( EMPTY( $satisfier ) )
		{
			return false;
		}

		/*if( $this->minAge AND !$this->maxAge )
		{
			return $this->setMinAge($this->minAge);
		}
		else if( !$this->minAge AND $this->maxAge )
		{
			return $this->setMaxAge($this->maxAge);
		}
		else
		{
			$minCheck 	= $this->setMinAge( $this->minAge );
			$maxCheck 	= $this->setMaxAge( $this->maxAge );

			return ( $minCheck AND $maxCheck );
		}*/

		if( !is_array( $satisfier ) )
		{
			return $this->setMinAge($satisfier);
		}
		else
		{
			if( 
				( 
					isset( $satisfier[0] ) 
					&& !empty( $satisfier[0] )
				)
				&& 
				(
					isset( $satisfier[1] )  
					&& !empty( $satisfier[1] )
				)
			)
			{
				$minCheck = $this->setMinAge( $satisfier[0] );
				$maxCheck = $this->setMaxAge( $satisfier[1] );
				
				return ( $minCheck && $maxCheck );
			}
			else if(  
				( 
					!isset( $satisfier[0] )
					|| empty( $satisfier[0] )
				)
				&& 
				(
					isset( $satisfier[1] )
					&& !empty( $satisfier[1] )
				)
			)
			{
				if( is_numeric( $value ) )
				{
					return $this->setMaxAge( $satisfier[1] );
				}
				else
				{
					return $this->setMaxAge( $satisfier[1] );
				}
			}
			else
			{
				if( is_numeric( $value ) )
				{
					return $this->setMinAge( $satisfier[0] );
				}
				else
				{
					return $this->setMinAge( $satisfier[0] );
				}
			}
		}

	}

	public function validate( $value )
	{
		$satisfier = array( $this->minAge, $this->maxAge );

		$check = $this->run( $value, $satisfier );

		if( is_array( $check ) )
		{
			return $check['check'];
		}

		return $check;
	}

	private function createDateTimeFromAge($age)
    {
        $interval = sprintf('-%d years', $age);

        return new DateTime($interval);
    }

    private function setMaxAge($maxAge)
    {
    	// $this->maxAge = $maxAge;

    	if( null === $maxAge ) 
    	{
            return;
        }

        if( is_numeric( $this->value ) )
        {
        	$minDate = $maxAge;
        }
        else
        {
	        $minDate = $this->createDateTimeFromAge($maxAge);
	        $minDate->setTime(0, 0, 0);
	    }

        $len = Vefja::instance('AJD_validation\\Rules\\Maxlength_rule', [$this->inclusive]);

        $check = $len->run( $this->value, $minDate, $this->field );

        if(is_array($check))
        {
        	return $check['check'];
        }
        else
        {
        	return $check;
        }
    }

    private function setMinAge($minAge)
    {
    	// $this->minAge = $minAge;

	  	if( null === $minAge ) 
	  	{
            return;
        }

        if( is_numeric( $this->value ) )
        {
        	$maxDate = $minAge;
        }
        else
        {
	     	$maxDate = $this->createDateTimeFromAge($minAge);
	        $maxDate->setTime(23, 59, 59);
	    }

        $len = Vefja::instance('AJD_validation\\Rules\\Minlength_rule', [$this->inclusive]);

        $check = $len->run( $this->value, $maxDate, $this->field );

        if(is_array($check))
        {
        	return $check['check'];
        }
        else
        {
        	return $check;
        }
        
    }
}