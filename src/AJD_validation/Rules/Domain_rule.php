<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_rule;
use AJD_validation\Contracts\Abstract_exceptions;

class Domain_rule extends Abstract_rule
{
	protected $tld;
	protected $checks 		= array();
	protected $otherParts;

	public function __construct($tldCheck = TRUE)
	{
	 	$this->checks[] = $this->getValidator()->no_whitespace();
        $this->checks[] = $this->getValidator()->contains('.');
        $this->checks[] = $this->getValidator()->length(3, NULL);

		$this->tldCheck($tldCheck);

        $validator 		= $this->getValidator();

        $this->otherParts 	= $validator->alnum('-')
        						->inverse( $this->getValidator()->starts_with('-') )
        						->one_or( 
        							$this->getValidator()->inverse(
        								$this->getValidator()->contains('--')
        							),
        							$this->getValidator()->starts_with('xn--')
        								->callback( function( $str ) 
        								{
        									return substr_count($str, '--') == 1;
        								})

        						);
	}

	public function tldCheck($tldCheck = TRUE)
    {
    	if( $tldCheck === TRUE )
    	{
    		$this->tld 		= $this->getValidator()->tld();
    	}
    	else
    	{
    		$this->tld 		= $this->getValidator()->inverse(
    							$this->getValidator()->starts_with('-')
    						)
    						->no_whitespace()
    						->length(2, NULL);
    	}

    	return TRUE;
    }

	public function run( $value, $satisfier = NULL, $field = NULL, $clean_field = NULL )
	{
		$checks			= array();
		$appendError 	= '';
		$exceptions 	= array();

		foreach( $this->checks as $check )
		{
			if( !$check->validate( $value ) )
			{
				$checks[] 			= FALSE;
				$exceptionDetail 	= $this->getException( $check, $value );

				$exceptions 		= array_merge( $exceptionDetail['exceptions'], $exceptions );
			}
		}

		$parts 			= explode('.', $value );
		$checkParts 	= $parts;
		$assertParts 	= $parts;

		if( count( $parts ) < 2 
			OR !$this->tld->validate( array_pop( $checkParts ) )
		)
		{
			$checks[] 				= FALSE;
			
			$exceptionDetail 		= $this->getException( $this->tld, array_pop( $assertParts ) );

			$exceptions 			= array_merge( $exceptionDetail['exceptions'], $exceptions );
		}
		
		foreach( $parts as $part )
		{
			if( !$this->otherParts->validate( $part ) )
			{
				$checks[] 			= FALSE;
				$exceptionDetail 	= $this->getException( $this->otherParts, $part );

				$exceptions 		= array_merge( $exceptionDetail['exceptions'], $exceptions );
			}
		}
		
		$check 		= !in_array( FALSE, $checks );

		$msg 				= $this->getExceptionError($value)
								->setRelated($exceptions)
								->getFullMessage(function( $messages )
								{
								 	$firstMessage   = str_replace('-', '', $messages[0]);
		                            $messages[0]    = $firstMessage;

		                            return implode('<br/>', $messages);
								});

		return array(
			'check'			=> $check,
			'msg'			=> $msg
		);

	}

	protected function getException( $rule, $value )
    {
    	$errors 			= '';
    	$exceptions 		= array();

    	try
    	{
    		$rule->setName($value)->assertErr( $value, TRUE );
    	}
    	catch( Abstract_exceptions $e )
    	{
    		$exceptions[] 	= $e;
    	}
    	
	 	return array(
	 		'exceptions'	=> $exceptions
	 	);
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