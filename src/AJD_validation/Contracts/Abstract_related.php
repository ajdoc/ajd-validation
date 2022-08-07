<?php namespace AJD_validation\Contracts;

use AJD_validation\AJD_validation as v;
use AJD_validation\Contracts\Abstract_rule;
use AJD_validation\Contracts\Rule_interface;
use AJD_validation\Contracts\Abstract_exceptions;

abstract class Abstract_related extends Abstract_rule
{
	public $mandatory = TRUE;
	public $relation  = '';
	public $validator;

 	abstract public function hasRelation($value);

    abstract public function getRelationValue($value);

	public function __construct($relation, Rule_interface $validator = NULL, $mandatory = TRUE)
	{
        $this->setName($relation);

        if( $validator AND !$validator->getName() ) 
        {
            $validator->setName($relation);
        }

        $this->relation  = $relation;
        $this->validator = $validator;
        $this->mandatory = $mandatory;
	}
    
	public function setName($name)
	{
	    parent::setName($name);

	    if($this->validator instanceof Rule_interface) 
	    {
	        $this->validator->setName($name);
	    }

	    return $this;
	}

	private function decision($type, $hasRelation, $value, $override = FALSE)
    {
        $relationValue  = $this->getRelationValue( $value );

    	return ( !$this->mandatory AND !$hasRelation )
				    OR ( IS_NULL( $this->validator ) 
					   OR ( $type === 'assertErr' ) ? $this->validator->{$type}( $relationValue, $override ) :  $this->validator->{$type}( $relationValue )
				);
    }

    public function run( $value, $satisfier = NULL, $field = NULL, $clean_field = NULL )
    {
    	$hasRelation 	= $this->hasRelation($value);
    	$check 			= FALSE;
        $append_error   = '';

    	if( $this->mandatory AND !$hasRelation ) 
    	{
    		$check 		= FALSE;
    	}
    	else
    	{
    		$check 		= $this->decision( 'run', $hasRelation, $value );

            if( !$check )
            {
                try
                {
                    $this->setName($this->relation)->assertErr( $value, TRUE );
                }
                catch( Abstract_exceptions $e )
                {
                    $append_error  = $e->getFullMessage(function($messages)
                    {
                        $firstMessage   = str_replace('-', '', $messages[0]);
                        $realMessage    = array();
                        $messages[0]    = $firstMessage;

                        foreach( $messages as $key => $message )
                        {
                            if( preg_match('/Data validation failed for/', $message) )
                            {
                                continue;
                            }

                            if( $key != 0 )
                            {
                                $message        = '<br/>&nbsp;&nbsp;&nbsp;&nbsp;'.$message;
                            }
                            else
                            {
                                $message        = preg_replace('/^[\s]/', '', $message);   
                                $message        = '&nbsp;&nbsp;&nbsp;&nbsp;- '.$message;
                            }

                            $realMessage[$key]  = $message;
                        }

                        return implode('', $realMessage);

                    });
                }
            }
    	}

        if(!empty($append_error))
        {
            return array(
                'check'     => $check,
                'append_error'  => '<br>'.$append_error
            );
        }
        else
        {
            return array(
                'check'     => $check,
            );
        }
    }

    public function validate($value)
    {
	 	$check              = $this->run( $value );

        if( is_array( $check ) )
        {
            return $check['check'];
        }

        return $check;
    }

    public function assertErr($value, $override = FALSE, $inverseCheck = null)
    {
        $hasRelation    = $this->hasReference($value);

        if($this->mandatory AND !$hasRelation) 
        {
            throw $this->getExceptionError($value, array('hasReference' => FALSE), NULL, $override);
        }

        try 
        {
            return $this->decision('assertErr', $hasRelation, $value, $override);
        } 
        catch(Abstract_exceptions $e) 
        {
            throw $this
                ->getExceptionError($this->relation, array('hasReference' => TRUE), NULL, $override)
                ->addRelated($e);
        }
    }
}