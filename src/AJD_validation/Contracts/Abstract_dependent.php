<?php namespace AJD_validation\Contracts;

use Exception;
use AJD_validation\AJD_validation as v;
use AJD_validation\Contracts\Abstract_rule;
use AJD_validation\Contracts\Rule_interface;
use AJD_validation\Contracts\Abstract_exceptions;
use AJD_validation\Contracts\Validator;
use AJD_validation\Exceptions\Nested_rule_exception;
use AJD_validation\Helpers\Array_helper;
use AJD_validation\Helpers\Validation_helpers;

abstract class Abstract_dependent extends Abstract_rule
{
	public $dependetFields;
	public $dependentValue;
	public $values;

	public $needsComparing 		= FALSE;
	public $comparator 			= '==';
	public $without 			= FALSE;
	public $any 				= TRUE;
	public $showSubError 		= FALSE;


	public $fieldsDisplay 		= '';
	public $valueDisplay 		= '';

	protected $validator;
	protected $checkValidator;
	protected $fieldCheckValidator;

	public function __construct($dependetFields, array $dependentValue = array(), array $values = array(), Rule_interface $checkValidator = NULL, Rule_interface $validator = NULL)
	{
		$this->dependetFields 	= $dependetFields;
		$this->dependentValue 	= $dependentValue;
		$this->values 			= $values;

		$requiredValidator 		= $this->getValidator()->required();

		if( $this->needsComparing AND !$requiredValidator->validate( $this->dependentValue ) )
		{
			throw new Exception( 'Value(s) for comparing is required.' );
		}
		else 
		{
			$this->valueDisplay = implode(', ', $this->dependentValue);
		}

		if( is_array( $this->dependetFields ) )
		{
			$this->fieldsDisplay 	= implode(', ', $this->dependetFields);
		}
		else
		{
			$this->fieldsDisplay 	= $this->dependetFields;
		}

		if( !EMPTY( $checkValidator ) )
		{
			$this->checkValidator 	= $checkValidator;
		}
		else
		{
			$this->checkValidator 	= $this->getValidator()->required();
		}

		if( !EMPTY( $validator ) )
		{
			$this->validator 		= $validator;
		}
		else
		{
			$this->validator 		= $this->getValidator()->required();
		}

		$this->fieldCheckValidator 	= $this->getValidator()->one_or(Validator::contains('.'), Validator::contains('*'));
	}

	public function run( $value, $satisfier = NULL, $field = NULL, $clean_field = NULL, $origValues = NULL )
	{
		$check 				= TRUE;

		$result 			= array();
		$msg 				= '';
		
		if(!empty($this->values))
		{
			$origValues = $this->values;
		}

		if( !EMPTY( $this->dependetFields ) AND !EMPTY( $origValues ) )
		{
			$this->values 	= $origValues;

			$subCheck 		= $this->processDependency( $value );
			
			if( $subCheck['check'] )
			{
				$check 		= $this->validator->validate($value);
				
				if( !$check )
				{
					try
					{
						$this->validator->setName($clean_field)->assertErr( $value, TRUE );
					}
					catch( Nested_rule_exception $e )
					{
						$msg 	= $e->getFullMessage(function($messages)
                        {
                            $firstMessage   = str_replace('-', '', $messages[0]);
                            $realMessage    = array();
                            $messages[0]    = $firstMessage;

                            foreach( $messages as $key => $message )
                            {
                                if( $key != 0 )
                                {
                                    $message        = '<br/>&nbsp;&nbsp;'.$message;
                                }
                                else
                                {
                                    $message        = preg_replace('/^[\s]/', '', $message);   
                                    $message        = $message;
                                }

                                $realMessage[$key]  = $message;
                            }

                            return implode('', $realMessage);

                        });
					}
					catch( Abstract_exceptions $e )
					{
						$msg 	= $e->getExceptionMessage();
					}
				}
			}
			else
			{

				$check 		= FALSE;

				if( !EMPTY( $subCheck ) AND $this->showSubError )  
				{
					if(!$subCheck['check'])
					{
						if(!empty($subCheck['append_msg']))
						{
							$result['append_error'] 	= '<br/>&nbsp;&nbsp;&nbsp;- '.$subCheck['append_msg'];

						}
					}
				}
				else
				{

					$check 	= $subCheck['check'];
				}
			}
		}

		$result['check'] 	= $check;

		if( !EMPTY( $msg ) )
		{
			$result['msg']	= $msg;
		}
		
		return $result;

	}

	protected function processDependency( $value )
	{
		if( is_array( $this->dependetFields ) )
		{
			$checks 			= array();
			$append_msg 		= '';

			foreach( $this->dependetFields as $dependetFields )
			{
				if( $this->checkFieldHasRecursionDependency( $dependetFields ) )
				{
					$checkDet 		= $this->processCheckFieldRecursion( $dependetFields, $value );

					$checks 		= array_merge( $checks, $checkDet['check'] );

					$append_msg 	.= '<br/>&nbsp;&nbsp;&nbsp;&nbsp;- '.$checkDet['append_msg'];
				}
				else
				{
					$checkDet 		= $this->processCheckField( $dependetFields, $value );

					$checks[] 		= $checkDet['check'];
					$append_msg 	.= '<br/>&nbsp;&nbsp;&nbsp;&nbsp;- '.$checkDet['append_msg'];
				}
			}

			$append_msg 		= ltrim( $append_msg, '<br/>&nbsp;&nbsp;&nbsp;&nbsp;- ' );
			
			if( $this->any )
			{
				$check 	= in_array(TRUE, $checks);
			}
			else
			{
				$check 	= !in_array(FALSE, $checks);
			}

			return array(
				'check' 		=> $check,
				'append_msg'	=> $append_msg
			);
		}
		else
		{
			if( $this->checkFieldHasRecursionDependency( $this->dependetFields ) )
			{
				$checkDet = $this->processCheckFieldRecursion( $this->dependetFields, $value );

				if( $this->any )
				{
					$check 	= in_array(TRUE, $checkDet['check']);
				}
				else
				{
					$check 	= !in_array(FALSE, $checkDet['check']);
				}

				$append_msg 		= ltrim( $checkDet['append_msg'], '<br/>&nbsp;&nbsp;&nbsp;&nbsp;- ' );

				return array(
					'check' 		=> $check,
					'append_msg'	=> $append_msg
				);
			}
			else
			{
				$check 	= $this->processCheckField( $this->dependetFields, $value );

				$append_msg 		= ltrim( $check['append_msg'], '<br/>&nbsp;&nbsp;&nbsp;&nbsp;- ' );

				return array(
					'check' 		=> $check['check'],
					'append_msg'	=> $append_msg
				);
			}
		}
	}

	protected function processCheckFieldRecursion( $dependetFields, $value )
	{
		$checks 	= array();
		$append_msg = '';

		static $check   = FALSE;

		$data 			= Validation_helpers::initializeProcessData($dependetFields, $this->values);

		foreach( $data as $subField => $d )
		{
			if( is_array( $d ) )
			{
				$checkDetails 		= $this->processCheckFieldRecursion( $subField.'.*', $this->values );

				$checks 			= array_merge( $checks, $checkDetails['check'] );

				$append_msg 		.= $checkDetails['append_msg'];

			}
			else
			{
				if( $this->checkValidator->validate( $d ) )
				{
					if( $this->needsComparing )
					{
						$checkComp 	= $this->processComparing( $d );

						$subCheck 	= in_array(TRUE, $checkComp);

						$check 		= $subCheck;

						$checks[] 	= $subCheck;
					}
					else
					{
						if( $this->without )
						{
							$checks[] 	= FALSE;
						}
						else
						{
							$checks[] 	= TRUE;
						}

					}
				}
				else
				{
					if( $this->without )
					{
						$checks[] 	= TRUE;
					}
					else
					{
						$checks[] 	= FALSE;
					}

					try
					{
						$this->checkValidator->setName( $subField )->assertErr( $d, TRUE );
					}
					catch( Nested_rule_exception $e )
					{
						$append_msg .= $e->getFullMessage(function($messages)
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
	                                $message        = '<br/>&nbsp;&nbsp;'.$message;
	                            }
	                            else
	                            {
	                                $message        = preg_replace('/^[\s]/', '', $message);   
	                                $message        = $message;
	                            }

	                            $realMessage[$key]  = $message;
	                        }

	                        return implode('', $realMessage);

	                    });
					}
				}
			}

		}

		return array( 
			'check' 		=> $checks,
			'append_msg'	=> $append_msg
		);
	}

	protected function processCheckField( $dependetFields, $value )
	{
		$check 		= FALSE;
		$append_msg = '';

		if( ISSET( $this->values[ $dependetFields ] ) )
		{
			if( $this->checkValidator->validate( $this->values[ $dependetFields ] ) )
			{
				if( $this->without )
				{
					$check 			= FALSE;
				}
				else
				{
					$check 			= TRUE;
				}

				if( $this->needsComparing )
				{
					$checks 	= $this->processComparing( $this->values[ $dependetFields ], $dependetFields );

					$subCheck 	= in_array(TRUE, $checks);

					$check 		= $subCheck;
				}
			}
			else
			{
				if( $this->without )
				{
					$check 		= TRUE;
				}

				/*if( $this->needsComparing )
				{
					$checks 	= $this->processComparing( $this->values[ $dependetFields ] );

					$subCheck 	= in_array(TRUE, $checks);

					$check 		= $subCheck;
				}*/

				try
				{
					$this->checkValidator->setName( $dependetFields )->assertErr( $this->values[ $dependetFields ], TRUE );
				}
				catch( Nested_rule_exception $e )
				{

					$append_msg = $e->getFullMessage(function($messages)
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
                                $message        = '<br/>&nbsp;&nbsp;'.$message;
                            }
                            else
                            {
                                $message        = preg_replace('/^[\s]/', '', $message);   
                                $message        = $message;
                            }

                            $realMessage[$key]  = $message;
                        }

                        return implode('', $realMessage);

                    });

				}
			}

		}

		return array(
			'check' 		=> $check,
			'append_msg' 	=> $append_msg
		);
	}

	protected function processComparing( $dependentValue, $dependetFields = null )
	{
		$checks 	= array();

		if( !EMPTY( $this->dependentValue ) )
		{
			if(empty($dependetFields))
			{
				foreach( $this->dependentValue as $val )
				{
					$comparatorValidator 			= NULL;

					switch( $this->comparator )
					{
						case '==' :
							$comparatorValidator 	= $this->getValidator()->equals($val);
						break;

						case '!=' :
							$comparatorValidator	= $this->getValidator()->inverse( Validator::equals($val) );
						break;
					}

					$checks[] 						= $comparatorValidator->validate( $dependentValue );
				}
			}
			else
			{
				if(isset($this->dependentValue[$dependetFields]))
				{
					$val = $this->dependentValue[$dependetFields];
					switch( $this->comparator )
					{
						case '==' :
							$comparatorValidator 	= $this->getValidator()->equals($val);
						break;

						case '!=' :
							$comparatorValidator	= $this->getValidator()->inverse( Validator::equals($val) );
						break;
					}

					$checks[] 						= $comparatorValidator->validate( $dependentValue );
				}
			}
		}
		else
		{
			$checks[] 	= FALSE;
		}

		return $checks;
	}

	protected function checkFieldHasRecursionDependency( $dependetFields )
	{
		return $this->fieldCheckValidator->validate( $dependetFields );
	}

 	public function validate( $value )
    {	
        $check              = $this->run( $value );

        if( is_array( $check ) )
        {
            return $check['check'];
        }

        return $check;
    }
}