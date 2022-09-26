<?php namespace AJD_validation\Contracts;

use AJD_validation\AJD_validation as v;
use AJD_validation\Contracts\Abstract_rule;
use AJD_validation\Contracts\Rule_interface;
use AJD_validation\Contracts\Abstract_exceptions;

abstract class Abstract_related extends Abstract_rule
{
    public $mandatory = true;
    public $relation = '';
    public $validator;

    abstract public function hasRelation($value);

    abstract public function getRelationValue($value);

    public function __construct($relation, Rule_interface $validator = null, $mandatory = true)
    {
        $this->setName($relation);

        if( $validator AND !$validator->getName() ) 
        {
            $validator->setName($relation);
        }

        $this->relation = $relation;
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

    private function decision($type, $hasRelation, $value, $override = false)
    {
        $relationValue = $this->getRelationValue( $value );

        $typeResult = true;

        if(!is_null($this->validator))
        {
            $typeResult = ( $type === 'assertErr' ) ? $this->validator->{$type}( $relationValue, $override ) : $this->validator->{$type}( $relationValue );
        }

        return ( !$this->mandatory && !$hasRelation )
                    || ( is_null( $this->validator ) 
                       || $typeResult
                );
    }

    public function run( $value, $satisfier = null, $field = null, $clean_field = null )
    {
        $hasRelation = $this->hasRelation($value);
        $check = false;
        $append_error = '';

        if( $this->mandatory && !$hasRelation ) 
        {
            $check = false;
        }
        else
        {
            $check = $this->decision( 'run', $hasRelation, $value );

            if( !$check )
            {
                try
                {
                    $this->setName($this->relation)->assertErr( $value, TRUE );
                }
                catch( Abstract_exceptions $e )
                {
                    $append_error = $e->getFullMessage(function($messages)
                    {
                        $firstMessage = str_replace('-', '', $messages[0]);
                        $realMessage = array();
                        $messages[0] = $firstMessage;

                        foreach( $messages as $key => $message )
                        {
                            if( preg_match('/Data validation failed for/', $message) )
                            {
                                continue;
                            }

                            if( $key != 0 )
                            {
                                $message = '<br/>&nbsp;&nbsp;&nbsp;&nbsp;'.$message;
                            }
                            else
                            {
                                $message = preg_replace('/^[\s]/', '', $message);   
                                $message = '&nbsp;&nbsp;&nbsp;&nbsp;- '.$message;
                            }

                            $realMessage[$key] = $message;
                        }

                        return implode('', $realMessage);

                    });
                }
            }
        }

        if(!empty($append_error))
        {
            return [
                'check' => $check,
                'append_error' => '<br>'.$append_error
            ];
        }
        else
        {
            return [
                'check' => $check,
            ];
        }
    }

    public function validate($value)
    {
        $check = $this->run( $value );

        if( is_array( $check ) )
        {
            return $check['check'];
        }

        return $check;
    }

    public function assertErr($value, $override = false, $inverseCheck = null)
    {
        $hasRelation = $this->hasReference($value);

        if($this->mandatory && !$hasRelation) 
        {
            throw $this->getExceptionError($value, array('hasReference' => false), null, $override);
        }

        try 
        {
            return $this->decision('assertErr', $hasRelation, $value, $override);
        } 
        catch(Abstract_exceptions $e) 
        {
            throw $this
                ->getExceptionError($this->relation, array('hasReference' => true), null, $override)
                ->addRelated($e);
        }
    }
}