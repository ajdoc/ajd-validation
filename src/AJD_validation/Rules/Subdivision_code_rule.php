<?php namespace AJD_validation\Rules;

use Exception;
use AJD_validation\Contracts\Abstract_wrapper;
use AJD_validation\Contracts\Abstract_common;

class Subdivision_code_rule extends Abstract_wrapper
{
	public $countryCode;
	protected $classFactory;

	public function __construct($countryCode)
    {
    	$this->countryCode 	= $countryCode;

    	$validator 			= $this->getValidator();

    	$paramValid 		= $validator->country_code();

    	if( ! $paramValid->validate($countryCode) )
    	{
    		throw new Exception(sprintf('"%s" is not a valid country code.', $countryCode));
    	}

    	$countryNameArr 	= $this->format_field_name($countryCode);
    	$subdvNamespace 	= __NAMESPACE__.'\\SubdivisionCode\\';
    	$subvDir 			= dirname( __FILE__ ).Abstract_common::DS.'SubdivisionCode'.Abstract_common::DS;
    	$realName 			= $countryNameArr['clean'].'_subdivision_code_rule';
    	$subDivPath 		= $subvDir.$realName.'.php';
    	$className 			= __NAMESPACE__.'\\SubdivisionCode\\'.$countryNameArr['clean'];

    	$classFactory 		= static::get_factory_instance()->get_instance(TRUE);

    	$classFactory->append_rules_namespace($subdvNamespace);

    	if( file_exists( $subDivPath )  )
    	{
    		$subDivClass 		= $classFactory->rules( $subDivPath, $realName );
    		
    		$this->validationRules = $subDivClass;
    	}
    }
}