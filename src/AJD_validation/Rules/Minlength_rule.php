<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_interval;

class Minlength_rule extends Abstract_interval
{
	public function __construct($length = 0, $inclusive = true, $isString = false)
    {
    	$this->inclusive 	= $inclusive;
    	$this->isString 	= $isString;
    }

	public function run( $value, $satisfier = NULL, $field = NULL )
	{
		if( is_array( $satisfier ) )
		{
			if( ISSET( $satisfier[0] ) )
			{
				$length 	= $satisfier[0];
			}

			if( ISSET( $satisfier[1] ) )
			{
				$this->inclusive = $satisfier[1];
			}
		}
		else
		{
			$length 	= $satisfier;
		}

		$forceString = FALSE;

		if( !$this->isString )
		{
			if( is_numeric( $value ) )
			{
				$this->isNumeric 	= TRUE;
			}
			else
			{
				$this->isString 	= TRUE;
			}	
		}
		else
		{
			$forceString 		= TRUE;
			$this->isNumeric 	= FALSE;
		}

		if( $forceString )
		{
			if ($this->inclusive) 
			{
				$check 		= strlen( $value ) >= $this->filterInterval( $length );
			}
			else
			{
				$check 		= strlen( $value ) > $this->filterInterval( $length );
			}
		}
		else
		{
			if ($this->inclusive) 
			{
	            $check 		= $this->filterInterval( $value ) >= $this->filterInterval( $length );
	        }
	        else
	        {
				$check 		= $this->filterInterval( $value ) > $this->filterInterval( $length );
			}
		}

		$response 		= array(
			'check' 	=> $check
		);

		if( $this->isString )
		{
			$response['append_error']	= 'character(s)';
		}

		return $response;
	}

	public function validate( $value )
	{
		$satisfier 	= array( $this->inclusive );

		$check 		= $this->run( $value, $satisfier );

		if( is_array( $check ) )
		{
			return $check['check'];
		}

		return $check;
	}


	public function getCLientSideFormat( $field, $rule, $jsTypeFormat, $clientMessageOnly = FALSE, $satisfier = NULL, $error = NULL, $value = NULL )
	{
		if( $jsTypeFormat == Abstract_interval::CLIENT_PARSLEY ) 
        {
			$js[$field][$rule]['rule']  = <<<JS
	            data-parsley-minlength="{$satisfier[0]}"
JS;
			
			$js[$field][$rule]['message'] = <<<JS
                data-parsley-minlength-message="$error"
JS;

		}

		$js                 = $this->processJsArr( $js, $field, $rule, $clientMessageOnly );
		
        return $js;
	}
}