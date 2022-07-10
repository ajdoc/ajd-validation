<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_rule;

use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\DNSCheckValidation;
use Egulias\EmailValidator\Validation\MultipleValidationWithAnd;
use Egulias\EmailValidator\Validation\RFCValidation;
use Egulias\EmailValidator\Validation\SpoofCheckValidation;

class Email_rule extends Abstract_rule
{
	protected $emailChecker;
	protected $eguilasValidationNamespace 	= 'Egulias\\EmailValidator\\Validation\\';
	protected $eguilasValidations 			= array(
		'RFCValidation',
		// 'DNSCheckValidation',
		'SpoofCheckValidation'
	);

	public function __construct($emailChecker = NULL)
    {
    	$this->emailChecker 	= $emailChecker;

    	$this->eguilasValidations[] = [
			$this, 'validateEmail'
		];
    }

    public function getemailChecker()
    {
    	if( !$this->emailChecker instanceof EmailValidator 
    		AND class_exists('Egulias\\EmailValidator\\EmailValidator')
    	)
    	{
    		 $this->emailChecker = new EmailValidator();
    	}

    	return $this->emailChecker;
    }

	public function run( $value, $satisfier = NULL, $field = NULL )
    {
	 	$emailChecker 		= $this->getemailChecker();

	 	$checkEm 			= FALSE;
	 	$errorMessage 		= '';

	 	$checkbByValidateEmail = null;

	 	if( is_string( $value ) )
	 	{
	 		$value 			= $this->Femail()
	 		 					->cacheFilter('value')
	 		 					->filterSingleValue( $value, TRUE );
	 	}

	 	if( !$emailChecker instanceof EmailValidator ) 
	 	{
	 		$checkEm 		= $this->validateEmail($value);
	 	}
	 	else if( !class_exists('Egulias\\EmailValidator\\Validation\\RFCValidation') ) 
	 	{
	 		$checkEm 		= $emailChecker->isValid($value);
	 	}
	 	else
	 	{
	 		$multipleArr 	= array();

	 		if( class_exists('Egulias\\EmailValidator\\Validation\\MultipleValidationWithAnd') )
	 		{
	 			foreach( $this->eguilasValidations as $emailValidation )
	 			{
	 				if( !is_array($emailValidation) && class_exists( $this->eguilasValidationNamespace.$emailValidation ) )
	 				{
	 					if( !ISSET( $multipleArr[$emailValidation] ) )
	 					{
	 						$reflection 					= new \ReflectionClass($this->eguilasValidationNamespace.$emailValidation);

	 						try
	 						{
	 							$multipleArr[$emailValidation] 	= $reflection->newInstanceArgs(array());
	 						}
	 						catch( \LogicException $e )
	 						{
	 							unset( $multipleArr[$emailValidation] );
	 						}
	 					}
	 				}
	 				else
	 				{
	 					if(isset($emailValidation[0])
	 						&& isset($emailValidation[1])
	 					)
	 					{
	 						if(method_exists($emailValidation[0], $emailValidation[1]))
	 						{
	 							$checkbByValidateEmail = $emailValidation[0]->{$emailValidation[1]}($value);
	 						}
	 					}
	 				}
	 			}
	 			
	 			if( !EMPTY( $multipleArr ) )
	 			{
	 				if( count( $multipleArr ) == 1 )
	 				{
	 					$checkEm 				= $emailChecker->isValid( $value, current( $multipleArr ) );

	 					if(!is_null($checkbByValidateEmail))
	 					{
	 						$checkEm = ($checkEm && $checkbByValidateEmail);
	 					}
	 				}
	 				else
	 				{
		 				$multipleValidations 	= new MultipleValidationWithAnd( array_values( $multipleArr ) );

		 				$checkEm 				= $emailChecker->isValid( $value, $multipleValidations );

		 				if(!is_null($checkbByValidateEmail))
	 					{
	 						$checkEm = ($checkEm && $checkbByValidateEmail);
	 					}
		 			}
	 			}
	 			else
	 			{
	 				if(!is_null($checkbByValidateEmail))
 					{
 						$checkEm = ($checkbByValidateEmail);
 					}
	 			}
	 		}
	 		else
	 		{
	 			$checkEm 	= $emailChecker->isValid($value, new RFCValidation());

	 			if(!is_null($checkbByValidateEmail))
				{
					$checkEm = ($checkbByValidateEmail);
				}
	 		}

	 		$errorInstance 	= $emailChecker->getError();

	 		if( $errorInstance )
	 		{
	 			$errorMessage 	= $errorInstance::REASON;
	 		}
	 	}

    	return array( 
    		'check' 		=> $checkEm,
    		'append_error'	=> $errorMessage
    	);
    }

    public function validate( $value )
    {
    	$satisfier 		= array( $this->emailChecker );

    	$check 			= $this->run( $value, $satisfier );

    	if( is_array( $check ) )
    	{
    		return $check['check'];
    	}

    	return $check;
    }

    public function validateEmail($value)
    {
    	return ( is_string($value) AND filter_var($value, FILTER_VALIDATE_EMAIL) );
    }

    public function getCLientSideFormat( $field, $rule, $jsTypeFormat, $clientMessageOnly = FALSE, $satisfier = NULL, $error = NULL, $value = NULL )
	{
		if( $jsTypeFormat == Abstract_rule::CLIENT_PARSLEY ) 
        {
	 		$js[$field][$rule]['rule'] =   <<<JS
	            data-parsley-type="email"
JS;

			$js[$field][$rule]['message'] = <<<JS
                data-parsley-type-message="$error"
JS;

		}

		$js                 = $this->processJsArr( $js, $field, $rule, $clientMessageOnly );
		
        return $js;
	}
}