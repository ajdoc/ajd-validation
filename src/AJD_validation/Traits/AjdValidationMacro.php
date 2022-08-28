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
	protected $signatureName = 'frommacro';
	protected static $storeErrorMessage = [];

	protected $arguments = [];

	/**
     * Set the Rules arguments.
     *
     * @param  array $args
     * @return self
     */
	public function setArguments(array $arguments)
    {
    	$this->arguments = $arguments;

    	return $this;
    }

	 /**
     * Register a custom macro as an ajd validation rule.
     *
     * @param  Closure $validator
     * @param  array $errorMessages
     * @param  string $ruleName
     * @return AJD_validation
     */
    public function registerAsRule(CLosure $validator, array $errorMessages, $ruleName = null, $autoRun = true)
    {
    	$that = $this;

    	$name = $that::getCurrentMacroName();

        if(!empty($ruleName))
        {
        	$name = $ruleName;
        }

        $anonName = $name.$this->signatureName;

        $this->ruleName = $anonName;

        if(!ajdv::hasAnonymousClass($anonName))
        {
	        static::$storeErrorMessage[$anonName] = $errorMessages;

	        $anonClassRule = new class($that, $validator, static::$storeErrorMessage) extends Abstract_anonymous_rule
	        {
	        	protected $mainObject;
	        	protected $validator;
	        	protected static $errorMessages;

	        	public function __construct($mainObject, CLosure $validator, array $errorMessages)
	        	{
	        		$this->mainObject = $mainObject;
	        		$this->validator = $validator;
	        		static::$errorMessages = $errorMessages;
	        	}

	        	public function __invoke($value, $satisfier = NULL, $field = NULL)
				{
					return call_user_func_array($this->validator->bindTo($this, self::class), func_get_args());
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
        	$this->callRule(static::getInverse(), $anonName, true);
        }

        return $that;
    }

    /**
     * Calls the custom macro registered as an ajd validation rule
     *
     * @param  bool $inverse
     * @param  string $passRulename
     * @return AJD_validation
     */
    public function callRule($inverse = false, $passRulename = null, $fromRegister = false)
    {
    	$ruleName = $this->ruleName;

    	if(!empty($passRulename))
    	{
    		$ruleName = $passRulename;
    		
    		if(!$fromRegister)
    		{
    			$ruleName = $passRulename.$this->signatureName;	
    		}
    		
    	}

    	if($inverse)
    	{
    		$ruleName = 'Not'.$ruleName;
    	}

    	if(!empty($this->arguments))
    	{
    		$this->{$ruleName}(...$this->arguments);
    	}
    	else
    	{
    		$this->{$ruleName}();
    	}

    	return $this;
    }
}