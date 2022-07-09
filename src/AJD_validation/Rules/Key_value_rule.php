<?php namespace AJD_validation\Rules;

use Exception;
use AJD_validation\Contracts\Abstract_rule;
use AJD_validation\Contracts\Rule_interface;
use AJD_validation\Contracts\Abstract_exceptions;
use AJD_validation\Contracts\Validator;

class Key_value_rule extends Abstract_rule
{
	public $comparedKey;
	public $formatComparekey;
	public $ruleName;
	public $baseKey;
	public $formatBaseKey;

	public function __construct( array $satisifer = array() )
	{
		$formatComparekey 	= $this->format_field_name( $satisifer[0] );
		$formatBaseKey 		= $this->format_field_name( $satisifer[2] );

		$this->comparedKey 		= $formatComparekey['orig'];
		$this->formatComparekey = $formatComparekey['clean'];
		$this->ruleName 		= $satisifer[1];
		$this->baseKey 			= $formatBaseKey['orig'];
		$this->formatBaseKey 	= $formatComparekey['clean'];
	}

	private function getRule( $value )
	{
		if( is_array( $value ) AND !ISSET( $value[ $this->comparedKey ] ) )
		{
			throw $this->getExceptionError( $this->formatComparekey );
		}

        
		if( is_array( $value ) AND !ISSET( $value[ $this->baseKey ] ) )
		{
			throw $this->getExceptionError( $this->formatBaseKey );
		}

		try
		{
			$rule 		= Validator::__callStatic($this->ruleName, array( $value[$this->baseKey] ) );
			$rule->setName($this->formatComparekey);
		}
		catch( Abstract_exceptions $e )
		{
			throw $this->getExceptionError( $value, array( 'component' => TRUE ), NULL, TRUE );
		}

		return $rule;
	}

	private function overwriteExceptionParams(Abstract_exceptions $exception)
    {
    	$params 		= array();

    	foreach( $exception->getParams() as $key => $value )
    	{
    		if( in_array( $key, array('template', 'translator') ) )
    		{
    			continue;
    		}

			$params[$key] = $this->baseKey;
    	}

    	if( $this->comparedKey )
    	{
    		$params['field'] 	= $this->comparedKey;
    	}

	 	$exception->configure($params);

        return $exception;
    }

    public function assertErr($value, $override = FALSE)
    {
        $rule 	= $this->getRule($value);

        try 
        {
            $rule->assertErr( $value[$this->comparedKey], $override );
        } 
        catch (Abstract_exceptions $e) 
        {
            throw $this->overwriteExceptionParams($e);
        }

        return TRUE;
    }

    public function run( $value, $satisifer = NULL, $field = NULL, $clean_field = NULL, $origValue = NULL )
   	{   		
   		$check 			= FALSE;
   		$append_error	= '';

   		try
   		{
            $realValue = $value;

            if( !is_array( $value ) AND !EMPTY( $origValue ) )
            {
                $realValue = $origValue;
            }

   			$rule 	= $this->getRule($realValue);
   			
   			$check 	= $rule->run( $realValue[$this->comparedKey], $satisifer, $field, $clean_field );
            
   			if( is_array( $check ) )
	        {
	            $check 	= $check['check'];
	        }

	        if( !$check )
	        {
	        	$this->assertErr($value, TRUE);
	        }
   		}
   		catch (Abstract_exceptions $e) 
        {
        	$append_error 	= $e->findMessages(array($this->ruleName.'_rule_exception'));
        }

        if( !EMPTY( $append_error ) )
        {
        	$append_error 	= $append_error[$this->ruleName.'_rule_exception'];
        }

        $result 			= array(
        	'check'			=> $check
        );

        if( !EMPTY( $this->ruleName ) )
        {
        	$result['msg'] 	= $append_error;
        }

        return $result;
   	}

   	public function validate( $value )
    {
        $check              = $this->run( $value );

        if( is_array( $check ) )
        {
            return $check['check'];
        }

        return $check;
    }
}