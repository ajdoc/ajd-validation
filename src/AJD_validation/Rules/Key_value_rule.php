<?php namespace AJD_validation\Rules;

use Exception;
use AJD_validation\Contracts\Abstract_rule;
use AJD_validation\Contracts\Rule_interface;
use AJD_validation\Contracts\Abstract_exceptions;
use AJD_validation\Contracts\Nested_rule_exception;
use AJD_validation\Contracts\Validator;

class Key_value_rule extends Abstract_rule
{
	public $comparedKey;
	public $formatComparekey;
	public $ruleName;
	public $baseKey;
	public $formatBaseKey;

	public function __construct( array $satisifer = [] )
	{
        $formatBaseKey = null;
		$formatComparekey = $this->format_field_name( $satisifer[0] );

        if(isset($satisifer[2]))
        {
            $formatBaseKey = $this->format_field_name( $satisifer[2] );
        }

		$this->comparedKey = $formatComparekey['orig'];
		$this->formatComparekey = $formatComparekey['clean'];
		$this->ruleName = $satisifer[1];

        if(!empty($formatBaseKey))
        {
            $this->baseKey = $formatBaseKey['orig'];
            $this->formatBaseKey = $formatComparekey['clean'];    
        }
	}

	private function getRule( $value )
	{
		if( is_array( $value ) && !ISSET( $value[ $this->comparedKey ] ) )
		{
			throw $this->getExceptionError( $this->formatComparekey );
		}
        
		if( is_array( $value ) && !ISSET( $value[ $this->baseKey ] ) )
		{
			throw $this->getExceptionError( $this->formatBaseKey );
		}

		try
		{
            $keyValue = $this->comparedKey;

            if(!empty($this->baseKey))
            {
                $keyValue = $this->baseKey;
            }
            
            $realValue = [null];

            if(isset($value[$keyValue]))
            {
                $realValue = [$value[$keyValue]];
            }
            
			$rule = Validator::__callStatic($this->ruleName, $realValue );

			$rule->setName($this->formatComparekey);
		}
		catch( Abstract_exceptions $e )
		{
			throw $this->getExceptionError( $value, array( 'component' => true ), null, true );
		}

		return $rule;
	}

	private function overwriteExceptionParams(Abstract_exceptions $exception)
    {
    	$params = [];

        $keyValue = $this->comparedKey;

        if(!empty($this->baseKey))
        {
            $keyValue = $this->baseKey;
        }

    	foreach( $exception->getParams() as $key => $value )
    	{
    		if( in_array( $key, array('template', 'translator') ) )
    		{
    			continue;
    		}

			$params[$key] = $keyValue;
    	}

    	if( $this->comparedKey )
    	{
    		$params['field'] = $this->comparedKey;
    	}

	 	$exception->configure($params);

        return $exception;
    }

    public function assertErr($value, $override = false, $inverseCheck = false)
    {
        $rule = $this->getRule($value);

        try 
        {
            if(!isset($value[$this->comparedKey]))
            {
                $realValue = null;    
            }
            else
            {
                $realValue = $value[$this->comparedKey];   
            }

            $rule->assertErr( $realValue, $override );
        } 
        catch (Abstract_exceptions $e) 
        {
            throw $this->overwriteExceptionParams($e);
        }

        return true;
    }

    public function run( $value, $satisifer = null, $field = null, $clean_field = null, $origValue = null )
   	{   		
   		$check = false;
   		$append_error = '';

   		try
   		{
            $realValue = $value;

            if( !is_array( $value ) && !EMPTY( $origValue ) )
            {
                $realValue = $origValue;
            }
            
   			$rule = $this->getRule($realValue);
            $valueReal = null;

            if(isset($realValue[$this->comparedKey]))
            {
                $valueReal = $realValue[$this->comparedKey];
            }
   			
   			$check = $rule->run( $valueReal, $satisifer, $field, $clean_field );
            
   			if( is_array( $check ) )
	        {
	            $check = $check['check'];
	        }

            if(!isset($realValue[$this->comparedKey]))
            {
                $check = false;
            }

	        if( !$check )
	        {
	        	$this->assertErr($value, true);
	        }
   		}
   		catch (Abstract_exceptions $e) 
        {
        	$append_error = $e->findMessages([$this->ruleName.'_rule_exception']);
        }

        if( !empty( $append_error ) )
        {
        	$append_error = $append_error[$this->ruleName.'_rule_exception'];
        }

        $result = [
        	'check' => $check
        ];

        if( !empty( $this->ruleName ) )
        {
        	$result['msg'] = $append_error;
        }

        return $result;
   	}

   	public function validate( $value )
    {
        $check = $this->run( $value );

        if( is_array( $check ) )
        {
            return $check['check'];
        }

        return $check;
    }
}