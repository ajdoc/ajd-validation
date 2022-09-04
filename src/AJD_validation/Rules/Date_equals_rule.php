<?php namespace AJD_validation\Rules;

use Exception;
use AJD_validation\Contracts\Abstract_date;

class Date_equals_rule extends Abstract_date
{
	public function __construct( array $options )  
	{
		$paramValidator = $this->getValidator()->required();
		
		if( !isset( $options[0] ) || !$paramValidator->validate( $options[0] ) )
		{
			throw new Exception('Date Comparison is required.');
		}

		$compareDate = $options[0];
		$dateFormat = null;

		if( isset( $options[1] ) && !empty( $options[1] ) )
		{
			$dateFormat = $options[1];
		}

		parent::__construct( $compareDate, $dateFormat, '=' );
	}
}