<?php 

namespace AJD_validation\Async;

use AJD_validation\AJD_validation as ajdv;
use AJD_validation\Async\PromiseHelpers;
use AJD_validation\Async\Promise;
use AJD_validation\Async\FailedPromise;
use AJD_validation\Async\Promise_interface;
use AJD_validation\Traits\Conditionable;
use \Closure;

class ValidationResult extends Promise
{
    use Conditionable;
       
    private $ajd;
    private $field;
    private $cleanField;
    private $ajdProp;

    protected $validationResult;

    private $isValid;
    private $validContent;
    private $messages;
    private $props = [
        'clean_field', 'value', 'valid', 'errors', 'ajd', 'ajd_prop'
    ];

    private $spl_object_id;

    public function __construct(callable $resolver = null, callable $cancel = null, $validationResult = null, $field = null)
    {
        if($resolver)
        {
            parent::__construct($resolver, $cancel);    
        }

        $this->validationResult = $validationResult;

        $this->setUpConfig($field);

    }

    /**
     * @param string ?$field 
     * @return void
     */
    protected function setUpConfig($field = null)
    {
        $this->spl_object_id = \spl_object_id($this);
        if(!empty($field))
        {
            $this->field = $field;
        }

        if(!empty($this->validationResult))
        {
            if(empty($this->field))
            {
                $fields = array_keys($this->validationResult);
                $this->field = $fields[0];    
            }

            if($this->field === 'spl_object_id##')
            {
                $origField = $this->field;
                $this->field = $this->spl_object_id;
                $this->setProp($origField, $this->field);
            }
            
            $this->cleanField = $this->validationResult[$this->field]['clean_field'] ?? $this->field;
            $this->validContent = $this->validationResult[$this->field]['value'] ?? null;
            $this->isValid = $this->validationResult[$this->field]['valid'] ?? false;
            $this->messages = $this->validationResult[$this->field]['errors'] ?? [];
            $this->ajd = $this->validationResult[$this->field]['ajd'] ?? null;

            $this->ajdProp = $this->validationResult[$this->field]['ajd_prop'] ?? [];
        }
    }

    /**
     * @param string $origField
     * @param string $newField
     * @return void
     */
    protected function setProp($origField, $newField)
    {
        
        foreach($this->props as $prop)
        {
            $this->validationResult[$newField][$prop] = $this->validationResult[$origField][$prop];
        }
    }

    /**
     * @param callable $catch
     * @return \AJD_validation\Async\PromiseValue
     */
    public function catch(callable $catch)
    {
        return PromiseHelpers::catch($catch);
    }

    /**
     * @param string $field
     * @return self
     */
    public function removeErrorMessage($field = null)
    {
        $currentField = $field ?? $this->field;

        $this->ajd->removeErrorMessage($currentField);

        return $this;
    }

    /**
     * @param bool $valid
     * @return self
     */
    public function setIsValid($valid)
    {
        $this->isValid = $valid;
        
        return $this;
    }

    /**
     * @param bool $valid
     * @return self
     */
    public function setValue($value)
    {
        $this->validContent = $value;
        
        return $this;
    }

    /**
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

     /**
     * @return \AJD_validation\AJD_validation
     */
    public function getAjD()
    {
        return $this->ajd;
    }

    /**
     * @return array
     */
    public function getAjdProp()
    {
        return $this->ajdProp;
    }

    /**
     * @param array $ajdProp
     * @return self
     */
    public function setAjdProp(array $ajdProp)
    {
        $this->ajdProp = $ajdProp;

        return $this;
    }

     /**
     * @param array $ajdProp
     * @return self
     */
    public function appendAjdProp(array $ajdProp)
    {
        $this->ajdProp = array_merge_recursive($this->ajdProp, $ajdProp);

        return $this;
    }

    /**
     * @param callable $map
     * @return self
     */
    public function mapValue(callable $map)
    {
        $that = $this;

        return $this->process(
            
            function ($validContent) use ($map, &$that)
            {
                $value = $map($validContent, $that);

                if($value instanceof Promise_interface)
                {
                    return $value;
                }

                return $that->valid($value);
            },
            function (array $messages) use(&$that) 
            {
                return $that->errors($messages);
            }
        );
    }

     /**
     * @param callable(A): B $processValid
     * @param callable(E[]): B $processErrors
     * @return B
     */
    public function process(callable $processValid, callable $processErrors) 
    {
        if (! $this->isValid) 
        {
            return $processErrors($this->messages);
        }

        return $processValid($this->validContent);
    }

    /**
     * @param string $message
     * @param string $rule
     * @param int $key
     * @return self
     */
    public function overrideErrorMessage($message, $rule, $key)
    {
        $messages = [];

        $messages[$this->field][$rule][$key]['errors'] = $message;
        $messages[$this->field][$rule][$key]['clean_field'] = $this->cleanField;

        $this->ajd->overrideErrorMessage($this->field, $messages);

        return $this;
    }

    /**
     * @return \AJD_validation\AJD_validation
     */
    public function getValidationDefinition()
    {
        $that = $this;
        
        return function() use(&$that)
        {
            $that->ajdProp['setUpFrom'] = $that->field;
            
            return $that->ajd->setAjdProp($that->ajdProp);
        };
    }

    /**
     * @param string $message
     * @return \AJD_validation\Async\FailedPromise
     */
    public function throwErrors($messages)
    {
        if($messages)
        {
            return PromiseHelpers::reject(new \Exception($messages));
        }
    }

    /**
     * @param callable $map
     * @return self
     */
    public function mapErrors(callable $map)
    {
        $that = $this;

        return $this->process(
            function ($validContent) use(&$that)
            {
                return $that->valid($validContent);
            },
            function (array $messages) use ($map, &$that)
            {
                $value = $map($messages, $that);

                if($value instanceof FailedPromise)
                {
                    return $value;
                }

                return $that->errors($value);
            }
        );
    }

     /**
     * @param B $validContent
     * @param string ?$field
     * @return self
     */
    public function valid($validContent, $field = null)
    {
        if($field)
        {
            $this->field = $field;
        }

        $this->validationResult[$this->field]['value'] = $validContent;
        $this->validationResult[$this->field]['valid'] = true;
        $this->validationResult[$this->field]['errors'] = [];
        $this->validationResult[$this->field]['ajd'] = $this->ajd;
        $this->validationResult[$this->field]['ajd_prop'] = $this->ajdProp;

        return new self(null, null, $this->validationResult);
    }

    /**
     * @param array $messages
     * @param string ?$field
     * @return self
     */
    public function errors(array $messages, $field = null)
    {
        if($field)
        {
            $this->field = $field;
        }

        $this->validationResult[$this->field]['value'] = [];
        $this->validationResult[$this->field]['valid'] = false;
        $this->validationResult[$this->field]['errors'] = $messages;
        $this->validationResult[$this->field]['ajd'] = $this->ajd;
        $this->validationResult[$this->field]['ajd_prop'] = $this->ajdProp;

        return new self(null, null, $this->validationResult);
    }

    /**
     * @param ValidationResult<E, callable(A): B> $apply
     * @return self<E, B>
     */
    public function apply(ValidationResult $apply)
    {
        $that = $this;
        return $apply->process(
            /**
             * @param callable(A): $validApply
             * @return self
             */
            function (callable $validApply) use(&$that)
            {
                return $that->mapValue($validApply);
            },
            /** @return self<E, B> */
            function (array $applyMessages) use(&$that)
            {
                return $that->process(
                    /** @param A $validContent */
                    function ($validContent) use ($applyMessages, &$that) 
                    {
                        return $that->errors($applyMessages);
                    },
                    function (array $messages) use ($applyMessages, &$that) 
                    {
                        return $that->errors(array_merge($applyMessages, $messages));
                    }
                );
            }
        );
    }

    /**
     * @param self<F, B> $that
     * @param callable(A, B): C $joinBothValid
     * @param callable(A): C $joinThisValid
     * @param callable(B): C $joinThatValid
     * @param callable(E[], F[]): G[] $joinErrors
     * @return self<G, C>
     */
    public function meet(ValidationResult $that, callable $joinBothValid, callable $joinThisValid, callable $joinThatValid, callable $joinErrors) 
    {
        $thisContent = $this->validContent;
        $thatContent = $that->validContent;

        if ($this->isValid && $that->isValid) 
        {
            /**
             * @var A $thisContent
             * @var B $thatContent
             */
            return $this->valid($joinBothValid($thisContent, $thatContent));
        }

        if ($this->isValid) 
        {
            /** @var A $thisContent */
            return $this->valid($joinThisValid($thisContent));
        }

        if ($that->isValid) 
        {
            /** @var B $thatContent */
            return $this->valid($joinThatValid($thatContent));
        }

        return $this->errors($joinErrors($this->messages, $that->messages));
    }

    /**
     * @param self<F, B> $that
     * @param callable(A, B): C $joinValid
     * @param callable(E[], F[]): G[] $joinErrors
     * @return self<G, C>
     */
    public function join(ValidationResult $that, callable $joinValid, callable $joinErrors)
    {
        if (! $this->isValid || ! $that->isValid) 
        {
            return $this->errors($joinErrors($this->messages, $that->messages));
        }

        /** @var A $thisContent */
        $thisContent = $this->validContent;
        /** @var B $thatContent */
        $thatContent = $that->validContent;

        return $this->valid($joinValid($thisContent, $thatContent));
    }

    /**
     * @param callable(A): self<E, B> $bind
     * @return self<E, B>
     */
    public function bind(callable $bind)
    {
        $that = $this;
        return $this->process(
            /** @param A $validContent */
            function ($validContent) use ($bind, &$that) 
            {
                return $bind($validContent, $that);
            },
            function (array $messages, &$that)
            {
                return $that->errors($messages);
            }
        );
    }

    /**
     * @param string $type
     * @param bool $check_arr
     * @return mixed
     */
    public function castValueTo(string $type, $check_arr = true)
    {
        if($this->isValid)
        {
            $validContent = ajdv::Fcast_to($type)
                ->cacheFilter($this->field.$this->spl_object_id)
                ->filterValue($this->validContent, true, true, $check_arr);

            $this->validContent = $validContent;
        }
        
        return $this;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return $this->isValid;
    }

    /**
     * @param array $storage
     * @param bool $append
     * @return mixed
     */
    public function getValue(array &$storage = [], $append = true)
    {
        if(!$this->isValid)
        {
            return $storage;
        }

        $validContent = $this->validContent;
        
        if($append)
        {
            if(!isset($this->validContent[$this->field]))
            {
                $storage[$this->field] = $this->validContent;   
            }

            if(is_array($this->validContent))
            {
                if(count($this->validContent) == 1)
                {
                    if(isset($this->validContent[0]))
                    {
                        $storage[$this->field] = $this->validContent[0];        
                    }
                }
                else
                {
                    if(isset($this->validContent[$this->field]))
                    {
                        $storage = array_merge($storage, $this->validContent);
                    }
                }
            }

            $validContent = $storage;
        }
        
        return $validContent;
    }

    /**
     * @param array $storage
     * @param bool $append
     * @return mixed
     */
    public function getFieldValue(array &$storage = [], $append = false)
    {
        return $this->getValue($storage, $append);
    }
}