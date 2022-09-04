<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_interval;

class Minlength_rule extends Abstract_interval
{
	protected $length;
	public function __construct($length = 0, $inclusive = true, $isString = false)
    {
    	$this->inclusive = $inclusive;
    	$this->isString = $isString;
    	$this->length = $length;
    }

	public function run( $value, $satisfier = null, $field = null )
	{
		if( is_array( $satisfier ) )
		{
			if( isset( $satisfier[0] ) )
			{
				$length = $satisfier[0];
			}

			if( isset( $satisfier[1] ) )
			{
				$this->inclusive = $satisfier[1];
			}
		}
		else
		{
			$length = $satisfier;
		}

		if(!isset($length))
		{
			$length = $this->interval;
		}

		if(!isset($length))
		{
			$length = $this->length;
		}

		$forceString = false;

		if( !$this->isString )
		{
			if( is_numeric( $value ) )
			{
				$this->isNumeric = true;
			}
			else
			{
				$this->isString = true;
			}	
		}
		else
		{
			$forceString = true;
			$this->isNumeric = false;
		}

		if( $forceString )
		{
			if ($this->inclusive) 
			{
				$check = strlen( $value ) >= $this->filterInterval( $length );
			}
			else
			{
				$check = strlen( $value ) > $this->filterInterval( $length );
			}
		}
		else
		{
			if ($this->inclusive) 
			{
	            $check = $this->filterInterval( $value ) >= $this->filterInterval( $length );
	        }
	        else
	        {
				$check = $this->filterInterval( $value ) > $this->filterInterval( $length );
			}
		}

		$response = [
			'check' => $check
		];

		if( $this->isString )
		{
			$response['append_error'] = 'character(s)';
		}

		return $response;
	}

	public function validate( $value )
	{
		$satisfier = [];
		$check = $this->run( $value, $satisfier );

		if( is_array( $check ) )
		{
			return $check['check'];
		}

		return $check;
	}


	public function getCLientSideFormat( $field, $rule, $jsTypeFormat, $clientMessageOnly = false, $satisfier = null, $error = null, $value = null )
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

		$js = $this->processJsArr( $js, $field, $rule, $clientMessageOnly );
		
        return $js;
	}
}