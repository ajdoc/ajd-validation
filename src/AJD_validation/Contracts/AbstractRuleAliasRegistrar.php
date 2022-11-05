<?php namespace AJD_validation\Contracts;

use AJD_validation\Contracts\Abstract_anonymous_rule;
use AJD_validation\Helpers\{
	Array_helper
};

abstract class AbstractRuleAliasRegistrar extends Abstract_anonymous_rule
{
	protected static $validationStorage = [];
	protected static $validation;
	protected static $exceptions = [];
	protected static $aliasErrorMessages;

	protected $customError = [];
	protected $clientSides = [];

	public function __construct(array $customError = [], array $clientSides = [])
	{
		$this->customError = $customError;
		$this->clientSides = $clientSides;
	}

	public function __invoke($value, $satisfier = null, $field = null, $clean_field = null, $origValue = null)
	{	
		static::$validation = static::$validationStorage[$this->calledAnonRule];

		$customError = $this->customError;
		
		if(empty(static::$validation))
		{
			return false;
		}

		$newValidator = $this->getValidator();

		foreach(static::$validation->getRules() as $rule)
		{
			$newRule = clone $rule;
			$newValidator->addRules([$newRule]);
		}

		static::$validation = $newValidator;
		
		try
		{
			$ruleNames = [];
			$rules = [];
			$ruleInstance = [];

			$field_arr = $this->format_field_name( $field );
			
			if($this->inverseCheck)
			{
				$validation = $this->getValidator();
				$rules = static::$validation->getRules();

				foreach($rules as $val)
				{
					$val->inverseCheck = $this->inverseCheck;
					$newValidation = $this->getValidator();

					$this->expressionSetter($val, $origValue, $satisfier);

					$validation->inverse($newValidation->addRules([$val]));
				}						
			}
			else
			{
				$validation = static::$validation;
				$rules = $validation->getRules();
			}
			
			foreach($rules as $rule)
			{
				$rule->inverseCheck = $this->inverseCheck;

				if(!$this->inverseCheck)
				{
					$this->expressionSetter($rule, $origValue, $satisfier);
				}

				$ruleClass = get_class($rule);
				$ruleNames[] = $ruleClass;
				$ruleInstance[$ruleClass] = $rule;
			}

			if(isset($this->clientSides[$this->calledAnonRule]) && !empty($this->clientSides[$this->calledAnonRule]))
			{
				foreach($this->clientSides[$this->calledAnonRule] as $ruleName => $clientValue)
				{
					if(in_array( $clientValue['ruleClass'], $ruleNames, true ) )
					{
						$realField = $field_arr['orig'] ?? $field;

						$custom_err = $clientValue['custom_err'] ?: '';

						$custom_err = static::replaceExrpressionPlaceholder(
							[
								'field' => $clean_field,
							],
							$custom_err
						);

						$ruleObj = $ruleInstance[$clientValue['ruleClass']] ?? null;
						$ruleNameWsuffix = $ruleName.'_'.static::$rules_suffix;
						$appendRule = \ucfirst($ruleName).'_'.static::$rules_suffix;

						if(!empty($ruleObj))
						{
							static::$cacheByFieldInstance[$realField][$appendRule] = $ruleObj;
						}
						
						if(isset(static::$ajd_prop[ 'js_rule' ][ $realField ][ $ruleNameWsuffix ]))
						{
							unset(static::$ajd_prop[ 'js_rule' ][ $realField ][ $ruleNameWsuffix ]);
						}

						static::plotCLientSide($ruleName, $realField, $clientValue['clientMessageOnly'], $realField, $clientValue['satisfier'], $custom_err);
					}
				}
			}
			
			$validation->setName($clean_field ?? $field)->assertErr($value, true);
		}
		catch(\AJD_validation\Contracts\Abstract_exceptions $e)
		{
			static::$exceptions['exceptions'] = $e;
			$exceptions = $e->getIterator();
			static::$exceptions['exceptionsArr'] = $exceptions;

			if(isset($customError[$this->calledAnonRule]) && !empty($customError[$this->calledAnonRule]))
			{
				$errors = $customError[$this->calledAnonRule];

				static::$exceptions['errors'][$this->calledAnonRule] = $errors;
			}

			static::$exceptions['inverse'][$this->calledAnonRule] = $this->inverseCheck;

			unset(static::$validatorCustomErrorMessages[$this->calledAnonRule]);

			return $this->inverseCheck ? true : false;
		}
		
		return $this->inverseCheck ? false : true;	
	}

	protected function expressionSetter(\AJD_validation\Contracts\Rule_interface $rule, $origValue = null, $satisfier = null)
	{
		if($rule instanceof \AJD_validation\Contracts\ExpressionRuleInterface)
		{
			if(is_array($origValue))
			{
				$rule->setExpressionArguments($origValue);
			}

			if(!empty($satisfier) && isset($satisfier[0]) && Array_helper::isAssoc($satisfier[0]))
			{
				$rule->callback(function($expression, $obj) use($satisfier)
				{
					$expression = static::replaceExrpressionPlaceholder(
						$satisfier[0],
						$expression,
						false
					);
					
					return $expression;
				});
			}
		}
	}

	public static function setAliasValidation(\AJD_validation\Contracts\Rule_interface $validation)
	{
		static::$validationStorage[static::$setAnonName] = $validation;
	}

	public static function getAnonName()
	{
		return static::$setAnonName;
	}

	public static function setAnonName($anonName)
	{
		static::$setAnonName = $anonName;
	}

	public static function setAliasErrorMessages(array $messages)
	{
		static::$aliasErrorMessages[static::$setAnonName] = $messages;
	}

	protected static function processAliasDefaultMessage($exceptions, $fromRule)
	{
		$message = '';
		$iterator = false;

		$exception = $exceptions['exceptions'];
		$exceptionsArr = $exceptions['exceptionsArr'];
		$errors = $exceptions['errors'][$fromRule] ?? [];
		$inverse = $exceptions['inverse'][$fromRule] ?? [];

		if($exception instanceof \SplObjectStorage)
		{
			$iterator = true;
			$messages = $exceptions;
		}
		else
		{
			$messages = $exception->getMessages();	
		}
		
		$newMessages = [];
		$marker = '-';
		$errorStore = [];

		if(!empty($exceptionsArr) && !empty($errors))
		{
			$i = 0;
			foreach($exceptionsArr as $ex)
			{
				$className = get_class($ex);
				$classNameArr = explode('\\', $className);
				$last = end($classNameArr);
				$clean = str_replace('_exception', '', $last);

				foreach($errors as $rule => $customMessages)
				{
					$ruleArr = explode('\\', $rule);
					$ruleName = end($ruleArr);

					if($ruleName == $clean)
					{
						$errorStore[$i] = $customMessages;
						$i++;
					}
				}
			}
		}

		foreach($messages as $index => $message)
		{
			$realMessage = $iterator ? $message->getExceptionMessage() : $message;

			if(isset($errorStore[$index]) && !empty($errorStore[$index]))
			{
				$realMessage = $inverse ? $errorStore[$index]['inverse'] : $errorStore[$index]['default'];
			}

			$errorParams = $exception->getParams();
			
			$realMessage = static::replaceExrpressionPlaceholder($errorParams, $realMessage);

			if($index == 0)
			{
				$newMessages[] = $realMessage;
				continue;
			}

			$realDepth = $iterator ? $messages[$message]['depth'] : 1;

			$depth = $realDepth;
	        $prefix = str_repeat('&nbsp;', $depth * 2);
	        $newMessages[] = sprintf('%s%s %s', $prefix, $marker, $realMessage);
		}

		$message = implode('<br/>', $newMessages);

		return $message;
	}

	public static function getAnonExceptionMessage(\AJD_validation\Contracts\Abstract_exceptions $exceptionObj)
	{
		$fromRule = $exceptionObj::getFromRuleName();

		$defaultMessage = '';
		$inverseMessage = '';

		if(isset(static::$aliasErrorMessages[$fromRule]) && !empty(static::$aliasErrorMessages[$fromRule]))
		{
			$defaultMessage = static::$aliasErrorMessages[$fromRule]['default'] ?? '';
			$inverseMessage = static::$aliasErrorMessages[$fromRule]['inverse'] ?? $defaultMessage;
		}
		else
		{
			if(static::$exceptions)
			{
				$defaultMessage = static::processAliasDefaultMessage(static::$exceptions, $fromRule);
				$inverseMessage = $defaultMessage;
			}
		}

		$exceptionObj::$defaultMessages = [
			 $exceptionObj::ERR_DEFAULT => [
			 	$exceptionObj::STANDARD => $defaultMessage,
			 ],
		  	 $exceptionObj::ERR_NEGATIVE => [
	            $exceptionObj::STANDARD => $inverseMessage,
	        ]
		];
	}
}