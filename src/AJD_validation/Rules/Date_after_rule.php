<?php namespace AJD_validation\Rules;

use Exception;
use AJD_validation\Contracts\Abstract_date;

class Date_after_rule extends Abstract_date
{
	public function __construct( array $options )  
	{
		$paramValidator = $this->getValidator()->required();

		if( !ISSET( $options[0] ) OR !$paramValidator->validate( $options[0] ) )
		{
			throw new Exception('Date Comparison is required.');
		}

		$compareDate 	= $options[0];
		$dateFormat 	= NULL;

		if( ISSET( $options[1] ) AND !EMPTY( $options[1] ) )
		{
			$dateFormat = $options[1];
		}
		
		$operator 		= '>=';

		if( ISSET( $options[2] ) AND !EMPTY( $options[2] ) )
		{
			$operator			= '>';
			$this->inclusive 	= TRUE;
		}
		
		parent::__construct( $compareDate, $dateFormat, $operator );
	}
}