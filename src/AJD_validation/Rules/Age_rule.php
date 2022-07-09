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

    public function __construct( $minAge = NULL, $maxAge = NULL, $inclusive = true )
    {
    	$this->minAge 	= $minAge;
    	$this->maxAge 	= $maxAge;
    	$this->inclusive = $inclusive;
    }  

	public function run( $value, $satisfier = NULL, $field = NULL )
	{
		$this->value 	= $value;
		$this->field 	= $field;

		if( EMPTY( $satisfier ) )
		{
			return FALSE;
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
					ISSET( $satisfier[0] ) 
					AND !EMPTY( $satisfier[0] )
				)
				AND 
				(
					ISSET( $satisfier[1] )  
					AND !EMPTY( $satisfier[1] )
				)
			)
			{
				$minCheck 	= $this->setMinAge( $satisfier[0] );
				$maxCheck 	= $this->setMaxAge( $satisfier[1] );

				return ( $minCheck AND $maxCheck );
			}
			else if(  
				( 
					!ISSET( $satisfier[0] )
					OR EMPTY( $satisfier[0] )
				)
				AND 
				(
					ISSET( $satisfier[1] )
					AND !EMPTY( $satisfier[1] )
				)
			)
			{
				if( is_numeric( $value ) )
				{
					return $this->setMinAge( $satisfier[1] );
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
					return $this->setMaxAge( $satisfier[0] );
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
		$satisfier 	= array( $this->minAge, $this->maxAge );

		$check 		= $this->run( $value, $satisfier );

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

    	if( NULL === $maxAge ) 
    	{
            return;
        }

        if( is_numeric( $this->value ) )
        {
        	$minDate 	= $maxAge;
        }
        else
        {
	        $minDate 	= $this->createDateTimeFromAge($maxAge);
	        $minDate->setTime(0, 0, 0);
	    }

        $minLen 	= Vefja::singleton('AJD_validation\\Rules\\Minlength_rule', array($this->inclusive));

        return $minLen->run( $this->value, $minDate, $this->field );
    }

    private function setMinAge($minAge)
    {
    	// $this->minAge = $minAge;

	  	if( NULL === $minAge ) 
	  	{
            return;
        }

        if( is_numeric( $this->value ) )
        {
        	$maxDate 	= $minAge;
        }
        else
        {
	     	$maxDate 	= $this->createDateTimeFromAge($minAge);
	        $maxDate->setTime(23, 59, 59);
	    }

        $maxLen 	= Vefja::singleton('AJD_validation\\Rules\\Maxlength_rule', array($this->inclusive));

        return $maxLen->run( $this->value, $maxDate, $this->field );
    }
}