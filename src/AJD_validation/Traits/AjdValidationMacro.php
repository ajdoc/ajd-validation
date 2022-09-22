<?php

namespace AJD_validation\Traits;

use AJD_validation\AJD_validation as ajdv;
use AJD_validation\Contracts\Abstract_anonymous_rule;
use AJD_validation\Contracts\Abstract_exceptions;
use Closure;

trait AjdValidationMacro
{
	use CanMacro;

	protected $ruleName;
	protected static $signatureName = 'frommacro';
	protected static $storeErrorMessage = [];

	protected $arguments = [];

	/**
     * Set the Rules arguments.
     *
     * @param  array $args
     * @param  string $name
     * @return self
     */
	public function setArguments(array $arguments, $name = null)
    {
    	$macroName = $this->getCurrentMacroName();

    	if(!empty($name))
    	{
    		$macroName = $name;
    	}

    	$this->arguments[$macroName] = $arguments;

    	return $this;
    }

	 /**
     * Register a custom macro as an ajd validation rule.
     *
     * @param  Closure $validator
     * @param  array $errorMessages
     * @param  string $ruleName
     * @param  bool $autoRun
     * @param  bool $fromRegisterer
     * @param  bool $bindObj
     * @return AJD_validation
     */
    public function registerAsRule(CLosure $validator, array $errorMessages, $ruleName = null, $autoRun = true, $fromRegisterer = false, $bindObj = true)
    {
    	$that = $this;

    	$name = $that::getCurrentMacroName();

        if(!empty($ruleName))
        {
        	$name = $ruleName;
        }

        $arguments = [];

    	if(!empty($name))
    	{
    		$arguments = $this->arguments[$name] ?? [];
    	}

        $anonName = $name;

        if(!$fromRegisterer)
        {
    		$anonName = $name.static::$signatureName;    	
        }

        $this->ruleName = $anonName;

        if(!ajdv::hasAnonymousClass($anonName))
        {
	        static::$storeErrorMessage[$anonName] = $errorMessages;

	        $anonClassRule = new class($that, $validator, static::$storeErrorMessage, $arguments, $bindObj) extends Abstract_anonymous_rule
	        {
	        	protected $mainObject;
	        	protected $validator;
	        	protected $bindObj;
	        	protected $arguments = [];
	        	protected static $errorMessages;

	        	public function __construct($mainObject, CLosure $validator, array $errorMessages, array $arguments, $bindObj = true)
	        	{
	        		$this->mainObject = $mainObject;
	        		$this->validator = $validator;
	        		$this->arguments = $arguments;
	        		$this->bindObj = $bindObj;
	        		static::$errorMessages = $errorMessages;
	        	}

	        	public function __invoke($value, $satisfier = NULL, $field = NULL)
				{	
					$closure = $this->validator;

					if($this->bindObj)
					{
						$closure = $this->validator->bindTo($this, self::class);
					}

					$arguments = array_merge(func_get_args(), $this->arguments);

					return call_user_func_array($closure, $arguments);
				}

				public static function getAnonName() : string
				{
					return static::$setAnonName;
				}

				public static function getAnonExceptionMessage(Abstract_exceptions $exceptionObj)
				{
					$fromRule = $exceptionObj::getFromRuleName();
					
					$defaultMessage = static::$errorMessages[$fromRule]['default'] ?? '';
					$inverseMessage = static::$errorMessages[$fromRule]['inverse'] ?? $defaultMessage;

					$exceptionObj::$defaultMessages 	= [
						 $exceptionObj::ERR_DEFAULT 			=> [
						 	$exceptionObj::STANDARD 			=> $defaultMessage,
						 ],
					  	 $exceptionObj::ERR_NEGATIVE 		=> [
				            $exceptionObj::STANDARD 			=> $inverseMessage,
				        ]
					];
				}

				public static function setAnonName($anonName)
				{
					static::$setAnonName = $anonName;
				}
	        };

	        $anonClassRule::setAnonName($anonName);

	        $that->registerAnonClass($anonClassRule);
	    }

        if($autoRun)
        {
        	$this->callRule(static::getInverse(), $anonName, true, $name);
        }

        return $that;
    }

    /**
     * Calls the custom macro registered as an ajd validation rule
     *
     * @param  bool $inverse
     * @param  string $passRulename
     * @param  string $normalName
     * @return AJD_validation
     */
    public function callRule($inverse = false, $passRulename = null, $fromRegister = false, $normalName = null)
    {
    	$ruleName = $this->ruleName;

    	if(!empty($passRulename))
    	{
    		$ruleName = $passRulename;
    		
    		if(!$fromRegister)
    		{
    			$ruleName = $passRulename.static::$signatureName;	
    		}
    		
    	}

    	if($inverse)
    	{
    		$ruleName = 'Not'.$ruleName;
    	}

    	$arguments = [];

    	if(!empty($normalName))
    	{
    		$arguments = $this->arguments[$normalName] ?? [];
    	}

    	if(!empty($arguments))
    	{
    		$this->{$ruleName}(...$arguments);
    	}
    	else
    	{
    		$this->{$ruleName}();
    	}

    	return $this;
    }

    /**
     * Retry an operation a given number of times.
     *
     * @param  int  $times
     * @param  callable  $callback
     * @param  int  $sleepMilliseconds
     * @param  callable|null  $when
     * @return mixed
     *
     * @throws \Exception
     */
    public static function retry($times, callable $callback, $sleepMilliseconds = 0, $when = null)
    {
        $attempts = 0;

        beginning:
        $attempts++;
        $times--;

        try 
        {
    		return $callback($attempts);
        } 
        catch (\Exception $e) 
        {
            if($times < 1 || ( $when && !$when($e) ) ) 
            {
                throw $e;
            }

            if($sleepMilliseconds) 
            {
                usleep($sleepMilliseconds * 1000);
            }

            goto beginning;
        }
    }
}