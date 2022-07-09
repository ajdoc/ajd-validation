<?php 

use AJD_validation\Contracts\Base_extension;
use AJD_validation\AJD_validation as v;

require 'AJD_validation.php';

$v 	= new AJD_validation;

try
{

	// example on how to use method chaining
	$v->required()
		->minlength(100)
		->check('username', '');

	$v->required()
		->minlength(100)
		->check('fname', '');

	// Another way of defining validation rules
	$v
		->Srequired()
			->field('username')
				->minlength(100)
			->field('fname')
				->minlength(150)
		->eSrequired()
		->checkGroup($_POST);

	// if both field has the same minumum lenght requirement
	$v
		->Srequired()
			->Sminlength(100)
				->field('username')
				->field('fname')
			->eSminlength()
		->eSrequired()
		->checkGroup($_POST);

	// using filters 
	// there must be a prefix (F) on the filter name
	// this will apply the define the filter after it runs the validation

	$v->Ftest()
		->required()
		->maxlength(2)
		->check('username', '');

	// if you want to run the filter before validating
	$v->Ftest(NULL, TRUE)
		->required()
		->maxlength(2)
		->check('username', '');

	// this will override the default error message for the rule
	$v->minlength(2, 'Custom error message runtime')
		->check('username', '');

	// if you want to get the values after validating
	$v->get_values();

	// if you want to check if a validation fails for a specific field
	$v->validation_fails('username'); // will return true/false

	// if you want to register a custom validation you have 4 ways

	// Registering a custom function can also register php function that returns true or false
	// check Function_factory file for valid built in php function
	$v->registerFunction('is_numeric', NULL, FALSE, TRUE);
	$v->add_rule_msg('is_numeric', 'this value is not numeric');

	// to use the function 
	$v->required()
		->is_numeric()
		->check('amount', 'a');

	// custom function using callback/Closure
	$v->registerFunction('my_custom_func', function($value, $field, $satisfier)
	{
		if( $value == 'a' )
		{
			return true;
		}
		else
		{
			return false;
		}
	});

	$v->add_rule_msg('my_custom_func', 'this value is not a');
	$v->my_custom_func()->check('custom', '');

	// Registering a Method 
	$custom_method 	= new Custom_method_rule;
	$v->registerMethod('custom_method', $custom_method);
	$v->add_rule_msg('custom_method', 'this value is not a');
	$v->custom_method()->check('custom_method', '');

	// Registering a Custom class
	// $path 	= dirname();
	$v->registerClass('Custom_class', '/path/to/class/', 'namespace');
	$v->add_rule_msg('custom_class', 'this value is not a');
	$v->custom_class()->check('custom_class', '');

	// Registering an extension
	$extension 	= new Custom_extension;
	$v->registerExtension($extension);
	$v->custom_validation()->check('custom_extension', '');

	// Adding macros for reusable set of rules
	$v->setMacro('test_macro', function( $ajd )
	{
		$ajd->minlength(2)
			->maxlength(30);
	});

	$v->macro('test_macro')->check('macro', '');

	// Or you can use this syntax

	$v->storeConstraintTo('group1')
			->Ftest( array(), TRUE )
			  ->required()
				->maxlength(30);

	$v->useContraintStorage('group1')->check('storage1', 'e');
	$v->useContraintStorage('group1')->check('storage2', '');

	// using middleware for conditional validation
	$v->setMiddleWare('test_middleware', function( $ajd, $func, $args )
	{
		$ajd2 	= new AJD_validation;
	
		$ajd2->required()->check('b', '');

		if( !$ajd2->validation_fails() )
		{

			$func($ajd, $args);
		}
	});

	$v->required()
		->minlength(2)
		->middleware('test_middleware','field', 'value');

	// using sometimes to run a rule if value is not empty
	$v->required()
		->minlength(2)->sometimes()
		->check('sometimes', '');


	// using sometimes to run a ralve depending on the condition
	$v->required()
		->minlength(2)->sometimes( function( $value, $satisfier, $field ) 
		{
			if( $value == 'a' )
			{
				return true;
			}
			else
			{
				return false;
			}
		})
		->check('sometimes', '');	
	// to throw Exception message 
	$v->assert();
}
catch( PDOException $e )
{
	echo $e->getMessage();
}
catch( Exception $e )
{
	echo $e->getMessage();
}

class Custom_extension extends Base_extension
{
	public function getName()
	{
		return 'Custom_extension';
	}

	public function runRules( $rule, $value, $satisfier, $field )
	{
		if( method_exists( $this , $rule ) )
		{
			return $this->{ $rule }( $value, $satisfier, $field );
		}
		else 
		{	
			return call_user_func_array( $rule , array( $value, $satisfier, $field ) );
		}
	}

	public function getRules()
	{
		return array(
			'custom_validation_rule'
		);
	}

	public function getRuleMessages()
	{
		return array(
			'custom_validation' 	=> 'The :field field is not a a.',
		);
	}

	public function custom_validation_rule( $value, $satisfier, $field )
	{
		if( $value == 'a' )
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}

// there must be a suffix _rule to the class name to avoid class conflict
class Custom_class_rule
{
	// must have a method run
	public function run( $value, $satisfier, $field )
	{
		if( $value == 'a' )
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}

class Custom_method_rule
{
	// there must be a suffix _rule to the method name to avoid method conflict
	public function customaa_method_rule( $value, $satisfier, $field )
	{
		if( $value == 'a' )
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}