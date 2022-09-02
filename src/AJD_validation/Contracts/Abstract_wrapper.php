<?php namespace AJD_validation\Contracts;

use AJD_validation\AJD_validation as v;
use AJD_validation\Contracts\Abstract_rule;
use AJD_validation\Contracts\Rule_interface;
use AJD_validation\Contracts\Abstract_common;

abstract class Abstract_wrapper extends Abstract_rule
{
	protected $validationRules;

	public function getValidationRules()
	{
		if( !$this->validationRules instanceof Rule_interface )
		{
			throw new Exception('There is no defined valid rules');
		}

		return $this->validationRules;
	}

	public function assertErr($value, $override = FALSE, $inverseCheck = null)
	{
		return $this->getValidatable()->assertErr($value, $override, $inverseCheck);
	}

	public function run( $value, $satisfier = NULL, $field = NULL, $clean_field = NULL )
	{
		$currClass 		= $this->getValidationRules();
		$check 			= $currClass->run( $value, $satisfier, $field );
		
		$err 			= static::get_errors_instance();

		$calledClass 	= get_class($currClass);

		$currExcept 	= str_replace('\\Rules\\', '\\Exceptions\\', $calledClass);
		$currExcept 	.= '_exception';

		$currNamespaceArr 	= explode('\\', $currExcept);
		$keys 				= array_keys($currNamespaceArr);
		$end 				= end($keys);
		unset($currNamespaceArr[ $end ]);
		
		$curNamespace 	= implode('\\', $currNamespaceArr);
		$curNamespace 	.= '\\';

		$currExceptDIr 	= dirname(dirname(__FILE__)).Abstract_common::DS.'Exceptions'.Abstract_common::DS.'SubdivisionCode'.Abstract_common::DS;

		$err->addExceptionNamespace( $curNamespace );
		$err->addExceptionDirectory( $currExceptDIr );

		$exception 		= $this->getExceptionError($value, array(), $currClass);

		$error 			= str_replace(array(':field'), array($clean_field), $exception->getExceptionMessage());

		$appendError    = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- '.$error.'<br/>';

		return array(
			'check' 		=> $check,
			'msg'			=> $error
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