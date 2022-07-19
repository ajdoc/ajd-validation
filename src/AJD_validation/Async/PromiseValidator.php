<?php 

namespace AJD_validation\Async;

use AJD_validation\Async\PromiseHelpers;
use AJD_validation\Async\Promise;
use AJD_validation\Traits\Events_dispatcher_trait;
use AJD_validation\Async\FailedPromise;

use \Closure;

class PromiseValidator extends Promise
{
	use Events_dispatcher_trait;

	protected $currentFiber;
	private $value;

	public function __construct(callable $resolver = null, callable $cancel = null, array $errors = [], $fiber = null, $value = null)
    {
    	$this->errors = $errors;
    	$this->value = $value;

    	if($resolver)
    	{
    		parent::__construct($resolver, $cancel);	
    	}
    	

    	if(!empty($fiber))
    	{
    		$this->currentFiber = $fiber;
    	}
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
}