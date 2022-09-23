<?php 

namespace AJD_validation\Async;

use AJD_validation\Async\PromiseHelpers;
use AJD_validation\Async\Promise;
use AJD_validation\Traits\Events_dispatcher_trait;
use AJD_validation\Async\FailedPromise;
use AJD_validation\Async\ValidationResult;
use AJD_validation\Traits\Conditionable;

use \Closure;

class PromiseValidator extends Promise
{
    use Events_dispatcher_trait;
    use Conditionable;

    protected $currentFiber;
    protected $Vfield;
    protected $fields = [];
    private $value;
    protected $validationResult;
    protected $resolverPass;

    public function __construct(callable $resolver = null, callable $cancel = null, array $errors = [], $fiber = null, $value = null, $field = null)
    {
        $this->errors = $errors;
        $this->value = $value;

        $this->field = $field;

        if($resolver)
        {
            parent::__construct($resolver, $cancel);    
        }

        if(!empty($fiber))
        {
            $this->currentFiber = $fiber;
        }
    }

    public function setField($field)
    {
        $this->Vfield = $field;
    }

    public function getField()
    {
        return $this->Vfield;
    }

    public function setFields(array $fields)
    {
        $this->fields = $fields;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function setFiber($fiber)
    {
        if(class_exists('Fiber'))
        {
            $this->currentFiber = $fiber;
        }
    }

    public function setValue($value = null)
    {
        $this->value = $value;

        return $this;
    }

    public function getFiber()
    {
        return $this->currentFiber;
    }

    public function catch(callable $catch)
    {
        return PromiseHelpers::catch($catch);
    }

    public function setValidationResult($validationResult)
    {
        $this->validationResult = $validationResult;
    }

    public function getValidationResult()
    {
        if(empty($this->validationResult))
        {
            return null;
        }

        if(is_array($this->validationResult) && !$this->validationResult instanceof ValidationResult)
        {
            $this->validationResult = new ValidationResult(null, null, $this->validationResult);    
        }

        return $this->validationResult;
    }
}