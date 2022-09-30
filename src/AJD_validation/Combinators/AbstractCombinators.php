<?php 

namespace AJD_validation\Combinators;

use AJD_validation\Combinators\CombinatorsInterface;
use AJD_validation\Async\ValidationResult;
use AJD_validation\Async\PromiseHelpers;
use AJD_validation\Async\Promise_interface;
use AJD_validation\Helpers\Array_helper;

abstract class AbstractCombinators implements CombinatorsInterface
{
	protected $validations;
	protected $validationDefinitions;
	protected $fields;
	protected $firstValidation;
	protected $combineErrorMessage;
	protected static $dontRunCheck = false;
	protected static $defaultGroup = 'defaultGroup';

	public function __construct(...$validations)
	{
		foreach($validations as $validation)
		{
			if($validation instanceof Promise_interface || $validation instanceof CombinatorsInterface)
			{
				$this->validations[] = $validation;	
			}
			else 
			{
				throw new InvalidArgumentException('Combinator muste be a \AJD_validation\Async\Promise_interface or \AJD_validation\Combinators\CombinatorsInterface');
			}
		}
	}

	public static function dontRunCheck($check = true)
	{
		static::$dontRunCheck = $check;
	}

	public function getValidations()
	{
		return $this->validations;
	}

	protected function extractValidationData(array $validations)
	{
		if(empty($validations))
		{
			return null;
		}

		$items = [];

		foreach($validations as $key => $validation)
		{
			if($validation instanceof CombinatorsInterface)
			{
				$validations = array_merge($validations, $validation->getValidations());
				unset($validations[$key]);
			}

			if($validation instanceof Promise_interface)
			{
				if(empty($this->firstValidation))
				{
					if(method_exists($validation, 'getValidationResult'))
					{
						$this->firstValidation = $validation->getValidationResult();	
					}
					else
					{
						$this->firstValidation = $validation;
					}

					continue;
				}
			}
		}

		foreach($validations as $validation)
		{			
			if(method_exists($validation, 'getValidationResult'))
			{
				$validation = $validation->getValidationResult();
			}

			if(!empty($validation))
			{
				$items[] = $validation;
				
				$field = $validation->getField();

				$this->fields[] = $field;

				$this->validationDefinitions[$field] = $validation;

				if(!static::$dontRunCheck)
				{
					$validation->removeErrorMessage();
				}
			}
		}

		return $items;
	}

	public function commonFirstValMapErrors($messages, $self)
	{
		$field = $self->getField();
		
		if(!isset($messages[$field]))
		{
			return [
				$field => $messages
			];
		}
		else
		{
			return $messages;
		}
	}

	protected function commonMapValues($value, $field)
	{
		if(!is_array($value))
		{
			return [$field => $value];
		}
		else
		{
			$countArr = count($value);

			if( $countArr == 1 && !Array_helper::isAssoc($value) )
			{
				return [$field => $value[0]];
			}

			return [ $field => $value ];
		}
	}

	protected function abstractCheck($value = null, $combinedFieldArray = null, $check_arr = true, $associative = false, array $validations = [])
	{
		$validations = $validations ?: $this->validations;

		$this->extractValidationData($validations);

		if(empty($this->validationDefinitions))
		{
			return;
		}

		$firstValidation =& $this->firstValidation;
		$that = $this;
		$promises = [];
		$emptyFirstMessage = false;
		
		if($firstValidation)
		{
			$firstValidation = $firstValidation->mapErrors(function($messages, $self) use($that, &$emptyFirstMessage)
			{
				if(empty($messages))
				{
					$emptyFirstMessage = true;
				}

				return $that->commonFirstValMapErrors($messages, $self);
			});
		}

		$toArray = false;

		if(!empty($combinedFieldArray) && is_array($value) && !$associative)
		{
			if(isset($value[$combinedFieldArray]))
			{
				$toArray = true;
				$value = $value[$combinedFieldArray];
			}
		}

		foreach($this->validationDefinitions as $field => $validationDefinitions)
		{
			$real_val = $value;

			if($toArray)
			{
				$real_val = [
					$field => $value
				];
			}

			if($associative)
			{
				if(!is_array($value))
				{
					$real_val = null;
				}
				else
				{
					if(isset($value[$field]))
					{
						$real_val = $value[$field];
					}
					else
					{
						$real_val = null;
					}
				}
			}

			$object = $validationDefinitions;

			if(!static::$dontRunCheck)
			{
				$validationDefinition = $validationDefinitions->getValidationDefinition();
				$object = $validationDefinition()->check($field, $real_val, $check_arr);
			}
		
			if( method_exists($object, 'getPromise') )
			{
				$promise = $object->getPromise();
			}
			else
			{
				$promise = $object;
			}

			if(method_exists($promise, 'getValidationResult'))
			{
				$validationResult = $promise->getValidationResult();
			}
			else
			{
				$validationResult = $promise;
			}

			$promises[] = $promise;

			if($firstValidation
				&& $firstValidation->getField() == $field)
			{
				if(method_exists($promise, 'passed'))
				{
					$promise->passed(function() use(&$firstValidation)
					{
						$firstValidation = $firstValidation->setIsValid(true);	
					})
					->fails(function() use(&$firstValidation, $associative, $emptyFirstMessage, $promise)
					{
						if($emptyFirstMessage)
						{
							$firstValidation = $promise->getValidationResult()->mapErrors([$this, 'commonFirstValMapErrors']);
						}
					});
				}
				else
				{
					if(static::$dontRunCheck)
					{
						$firstValidation = $firstValidation->setIsValid($promise->isValid());	
					}
					else
					{
						$firstValidation = $firstValidation->setIsValid(false);	
					}
				}

				if(static::$dontRunCheck)
				{
					$firstValidation = $firstValidation->mapValue(function($value) use($that, $field)
					{
						return $that->commonMapValues($value, $field);
					});
				}
			}
			
			if(
				$firstValidation
				&& $firstValidation->getField() != $field
			)
			{
				$firstValidation = $firstValidation
				->join(
					$validationResult,
					function($valueA, $valueB) use($that, $value, $field)
					{
						if(static::$dontRunCheck)
						{
							$valueBProcess = $that->commonMapValues($valueB, $field);
							
							return array_merge($valueA, $valueBProcess);
						}
						else
						{
							return $value;	
						}
					},
					function($errorsA, $errorsB) use($field)
					{
						$errorsBMain = [
							$field => $errorsB
						];
						
						return array_merge($errorsA, $errorsBMain);
					}
				);

				$firstValidation->appendAjdProp($validationResult->getAjdProp());
			}

			if($this->combineErrorMessage)
			{
				$validationResult->removeErrorMessage();
			}
		}
		
		return $this->createAllPromise($promises, $firstValidation);
	}

	protected function createAllPromise(array $promises, $firstValidation = null)
	{
		if(empty($promises))
		{
			return null;
		}

		$isValids = false;

		if(!static::$dontRunCheck)
		{
			$allPromise = PromiseHelpers::all($promises);
			$allPromise->then(function() use (&$isValids)
			{
				$isValids = true;
			},
			function() use (&$isValids)
			{
				$isValids = false;
			});
		}

		if($firstValidation)
		{
			$firstValidation->setIsValid($isValids);	
			$firstValidation = $this->combineErrorMessage($this->combineErrorMessage, $firstValidation);

			if(!static::$dontRunCheck)
			{
				$allPromise->setValidationResult($firstValidation);
			}
			else
			{
				$allPromise = $firstValidation;
			}
		}

		return $allPromise;
	}

	public function combineErrorMessage($message, $validationResult = null)
	{
		$toReturn = true;
		if(!$validationResult)
		{
			$toReturn = false;
			$this->extractValidationData($this->validations);

			$validationResult = $this->firstValidation;
		}

		if($message)
		{
			$validationResult = $validationResult->mapErrors(function($messages, $self) use($message)
			{
				$ajd = $self->getAjd();

				$currentMessage = $ajd->getPropMessage();

				$currentMessage = array_merge($currentMessage, [$message]);

				$ajd->setPropMessage($currentMessage);
				
				return [$message];
				
			});
		}

		if($toReturn)
		{
			return $validationResult;	
		}
	}

	public function setCombineErrorMessage($message)
	{
		$this->combineErrorMessage = $message;

		return $this;
	}

	public function check($value = null, $combinedFieldArray = null, $check_arr = true)
	{
		return $this->abstractCheck($value, $combinedFieldArray, $check_arr);
	}

	public function sequence($value = null, $combinedFieldArray = null, $check_arr = true, $associative = false)
	{
		$validations = $this->extractValidationData($this->validations);
		$that = $this;

		$firstValidation = $this->firstValidation;
		$promises = [];

		$groupValidations = $this->createFieldGroupings($validations, $value, $associative);

		$cnt = 0;

		foreach($groupValidations as $group => $validations)
		{			
			$break = false;

			if(empty($validations))
			{
				continue;
			}

			foreach($validations as $validation)
			{
				$this->clear();

				$realVal = isset($value[$group]) ? $value[$group] : $value;

				$object = $that->abstractCheck($realVal, $combinedFieldArray, $check_arr, $associative, [$validation]);

				if(property_exists($object, 'getPromise'))
				{
					$promise = $object->getPromise();
				}
				else
				{
					$promise = $object;
				}

				if($cnt == 0)
				{
					$firstValidation = $promise->getValidationResult();	
				}
				else
				{
					$firstValidation = $firstValidation->mapValue(function($validContent, $self) use($realVal)
					{
						return array_merge($validContent, $realVal);
					});

					if(!$promise->getValidationResult()->isValid())
					{
						$firstValidation->setIsValid(false);

						$firstValidation = $firstValidation->join(
							$promise->getValidationResult(),
							function(){},
							function($errorsA, $errorsB)
							{
								return $errorsB;
							}
						);
					}
				}
				
				$promises[] = $promise;

				$promise->then(
					function()
					{
					}, 
					function($e) use(&$break)
					{
						$break = true;
					}
				);

				if($group == static::$defaultGroup)
				{
					if($break)
					{
						break;
					}
				}
			}

			$cnt++;

			if($break)
			{
				break;
			}
		}

		return $this->createAllPromise($promises, $firstValidation);
	}

	public function associative(array $value = [], $check_arr = true)
	{
		return $this->abstractCheck($value, null, $check_arr, true);
	}

	public function assocSequence(array $value = [], $check_arr = true)
	{
		return $this->sequence($value, null, $check_arr, true);
	}

	protected function createFieldGroupings(array $validations, $value = null, $associative = true)
	{
		$groupValidations = [];
		$checkValue = false;

		if( $associative && is_array($value) )
		{
			$valuesDot = Array_helper::dot($value);
			$valueKeys = array_keys($valuesDot);

			foreach($valueKeys as $valKey)
			{
				$perExplodeDot = explode('.', $valKey);
				$perExplodeCnt = count($perExplodeDot);	
				if($perExplodeCnt > 2)
				{
					throw new \InvalidArgumentException('Deeper Nested arrays are not supported.');
				}
			}

			$currentVal = current($valueKeys);
			$explodeDots = explode('.', $currentVal);
			$cntExplode = count($explodeDots);
			$checkValue = ( $cntExplode == 2 );

			if( $checkValue )
			{
				$groupValidations = [];
				$cnt = 0;

				foreach($value as $group => $val)
				{
					foreach( $val as $groupField => $fieldVal )
					{
						if(!isset($validations[$cnt]))
						{
							continue;
						}

						$validation = $validations[$cnt];
						$field = $validation->getField();

						if($field == $groupField)
						{
							$groupValidations[$group][] = $validation;
						}

						$cnt++;
					}
				}
			}
		}

		if(!$checkValue)
		{
			$groupValidations[static::$defaultGroup] = $validations;
		}

		return $groupValidations;
	}

	protected function clear()
	{
		$this->validations = [];
		$this->validationDefinitions = [];
		$this->fields = [];
	}
}