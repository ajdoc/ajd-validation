<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_rule;
use AJD_validation\Helpers\Array_helper;
use AJD_validation\Helpers\Validation_helpers;

class Distinct_rule extends Abstract_rule
{
	public $origData;

	public function __construct( $origData = array() )
	{
		$this->origData 	= $origData;
	}

	public function run( $value, $satisfier = NULL, $field = NULL, $clean_field = NULL, $origData = NULL )
	{
		$check 		= FALSE;
		$subCheck 	= FALSE;

		if( !EMPTY( $origData ) )
		{
			$this->origData 	= $origData;
		}
		
		if( is_array( $this->origData ) AND !EMPTY( $this->origData ) )
		{
			$checkValidator = false;
			if(!empty($field))
			{
				$pattern 			= str_replace('\*', '[^.]+', preg_quote($field, '#'));

				$validator 			= $this->getValidator();
				$paramValidator 	= $validator->contains('.');
				$checkValidator 	= $paramValidator->validate($field);
			}
			
			if( $checkValidator )
			{
				$data 				= Array_helper::where(Array_helper::dot($this->origData), function ($value, $key) use ($field, $pattern) 
				{
					
	            	return $key != $field;
	        	});

	        	$subCheck 	= ( !in_array( $value, array_values($data) ) );
	        }
	        else
	        {
	        	$checks 	= array();

	        	if(is_array($value))
	        	{
	        		$duplicates = array_diff_assoc($value, array_unique($value));

	        		$subCheck = true;

	        		if(!empty($duplicates))
	        		{
	        			$subCheck = false;
	        		}
	        	}
	        	else
	        	{
		        	foreach( $this->origData as $val )
		        	{
	        			if( $val == $value )
		        		{
		        			$checks[] 	= TRUE;
		        		}	
		        	}

		        	$subCheck 	= ( count( $checks ) == 1 );
		        }
	        }
			
			if( !EMPTY( $value ) )
			{
        		$check 			= $subCheck;
        	}
        	else
        	{
        		$check 			= TRUE;
        	}
        	
		}
		else
		{
			if(is_array($value))
        	{
        		$duplicates = array_diff_assoc($value, array_unique($value));

        		$check = true;

        		if(!empty($duplicates))
        		{
        			$check = false;
        		}
        	}
		}
		
		return $check;
	}

	public function validate( $value )
	{
		$check 					= $this->run( $value );

		if( is_array( $check ) )
		{
			return $check['check'];
		}

		return $check;
	}
}