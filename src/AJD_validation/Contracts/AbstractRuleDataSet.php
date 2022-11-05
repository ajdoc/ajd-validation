<?php namespace AJD_validation\Contracts;

use AJD_validation\Contracts\Abstract_rule;

abstract class AbstractRuleDataSet extends Abstract_rule
{
    protected $dataSets;
    protected $arguments;
    protected $newObjects;
    protected $registerNameDataSet;

    protected $fromValidatorObject = false;
    protected $runPrevalidate = false;

    public function __construct($dataSets, array $arguments = [], $registerNameDataSet = null, array $newObjects = [])
    {
        $this->dataSets = $dataSets;
        $this->arguments = $arguments;
        $this->registerNameDataSet = $registerNameDataSet;
        $this->newObjects = $newObjects;
    }

    public function run($value, $satisfier = null, $field = null, $clean_field = null, $origValues = null)
    {
        $newObjects = $this->newObjects;
        $dataSets = $this->dataSets;
        $arguments = $this->arguments;
        $registerNameDataSet = $this->registerNameDataSet;

        $field_arr = $this->format_field_name( $field );

        if(isset($satisfier[0]) && !empty($satisfier[0]))
        {
            $dataSets = $satisfier[0];
        }

        if(isset($satisfier[1]) && !empty($satisfier[1]))
        {
            $arguments = $satisfier[1];
        }

        if(isset($satisfier[2]) && !empty($satisfier[2]))
        {
            $registerNameDataSet = $satisfier[2];
        }

        if(isset($satisfier[3]) && !empty($satisfier[3]))
        {
            $newObjects = $satisfier[3];
        }

        if(empty($newObjects))
        {
            $check_arr = !is_array($value);

            if(!$this->fromValidatorObject)
            {
                $registerNameDataSet = '';
            }

            if(empty($field))
            {
                static::addDataSets($dataSets, $arguments, $this->inverseCheck, $registerNameDataSet);
            }

            $newObjects = $this->processDataSet($field, $value, $field_arr, $check_arr, false, true, [], $this->runPrevalidate);

            $this->newObjects = $newObjects;
        }

        if($this->runPrevalidate && !empty($newObjects))
        {
            $lastObject = end($newObjects);

            $preValidate = $lastObject->getPrevalidate();

            $value = $preValidate['value'] ?? $value;
        }

        $check = $this->processDataSetValidation($newObjects, $field_arr['orig'], $value, $this->valueKey ?? 0);
        
        $no_error_message = true;

        /*if(!$check)
        {
            $exceptionClass = '\\AJD_validation\Exceptions\\Extend_rule_exception';
            
            if(class_exists($exceptionClass))
            {
                $no_error_message = $this->createDataSetError($newObjects, $exceptionClass);    
            }
        }*/

        return [
            'check' => $check,
            'no_error_message' => $no_error_message
        ];
    }

    public function validate($value)
    {
        $this->fromValidatorObject = true;
        $this->runPrevalidate = true;

        $args = [$this->dataSets, $this->arguments, $this->newObjects];
        $check = $this->run( $value, $args );

        if( is_array( $check ) )
        {
            return $check['check'];
        }

        return $check;
    }

    public function getDataSets()
    {
        return $this->newObjects;
    }
}