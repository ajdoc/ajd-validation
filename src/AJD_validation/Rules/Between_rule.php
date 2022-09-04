<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_rule;
use AJD_validation\Vefja\Vefja;

class Between_rule extends Abstract_rule
{
	protected $minlength;
	protected $maxlength;

	public $minValue;
	public $maxValue;
	public $inclusive;

	public function __construct( $minValue = null, $maxValue = null, $inclusive = true )
	{
		$this->minValue = $minValue;
		$this->maxValue = $maxValue;
		$this->inclusive = $inclusive;
	}

	public function run( $value, $satisfier = null, $field = null )
	{
		$inclusive = true;

		if( is_array( $satisfier ) )
		{
			if( isset( $satisfier[0] ) )
			{
				if( is_array( $satisfier[0] ) )
				{
					$this->minValue = $satisfier[0][0];

					if( isset( $satisfier[0][1] ) )
					{
						$this->maxValue = $satisfier[0][1];
					}
				}
				else
				{
					$this->minValue = $satisfier[0];
				}
			}

			if( !is_array( $satisfier[0] ) )
			{
				if( isset( $satisfier[1] ) )
				{
					$this->maxValue = $satisfier[1];
				}
			}

			if( is_array( $satisfier[0] ) )
			{
				if( isset( $satisfier[1] ) )
				{
					$inclusive = $satisfier[1];
				}
			}
			else
			{
				if( isset( $satisfier[2] ) )
				{
					$inclusive = $satisfier[2];
				}
			}
		}
		else
		{
			$this->minValue = $satisfier;
		}

		$this->inclusive = $inclusive;

		$check = false;

	 	if( !is_null($this->minValue) && !is_null($this->maxValue) && $this->minValue > $this->maxValue ) 
	 	{
	 		$check = false;
        }

        $this->minlength = Vefja::instance('AJD_validation\\Rules\\Minlength_rule', [$this->inclusive]);
        $this->maxlength = Vefja::instance('AJD_validation\\Rules\\Maxlength_rule', [$this->inclusive]);

        if( !is_null( $this->minValue ) AND !is_null( $this->maxValue ) )
        {
        	$minCheck = $this->minlength->run( $value, [$this->minValue, $this->inclusive] );
        	$maxCheck = $this->maxlength->run( $value, [$this->maxValue, $this->inclusive] );
        	
        	if(is_array($minCheck) && is_array($maxCheck))
        	{
        		$check = ( $minCheck['check'] AND $maxCheck['check'] );
        	}
        	else
        	{
        		$check = ( $minCheck AND $maxCheck );	
        	}
        	
        }
        else if( !is_null( $this->minValue ) AND is_null( $this->maxValue ) )
        {
        	$minCheck = $this->minlength->run( $value, [$this->minValue, $this->inclusive] );

        	if(is_array($minCheck))
        	{
        		$check = $minCheck['check'];	
        	}
        	else
        	{
        		$check = $minCheck;	
        	}
        }
        else if( is_null( $this->minValue ) && !is_null( $this->maxValue ) )
        {
        	$maxCheck = $this->maxlength->run( $value, [$this->maxValue, $this->inclusive] );

        	if(is_array($minCheck))
        	{
        		$check = $minCheck['check'];
        	}
        	else
        	{
        		$check = $maxCheck;
        	}
        }

        return $check;
	}

	public function validate( $value )
	{
		$satisfier = [$this->minValue, $this->maxValue, $this->inclusive];

		$check = $this->run( $value, $satisfier );

		if( is_array( $check ) )
		{
			return $check['check'];
		}

		return $check;
	}
}