<meta http-equiv="Cache-control" content="no-cache">

<?php

require 'vendor/autoload.php';

use AJD_validation\Contracts\Base_extension;
use AJD_validation\AJD_validation;

enum Status : string
{
    case DRAFT = 's';
    case PUBLISHED = 'b';
    case ARCHIVED = 'a';
}

enum Sstatus
{
    case DRAFT;
    case PUBLISHED;
    case ARCHIVED;
}

$v = new AJD_validation;

$v
	->addRuleDirectory(__DIR__.DIRECTORY_SEPARATOR.'CustomRules'.DIRECTORY_SEPARATOR)
	->addRuleNamespace('AJD_validationa\\');

$v
	->addRuleDirectory(__DIR__.DIRECTORY_SEPARATOR.'CustomRuless'.DIRECTORY_SEPARATOR)
	->addRuleNamespace('AJD_validation\\');


class Custom_extension extends Base_extension
{
	public function getName()
	{
		return 'Custom_extension';
	}

	public function getRules()
	{
		return array(
			'custom_validation_rule',
			'custom_validation2_rule'
		);
	}

	public function getRuleMessages()
	{
		return array(
			'custom_validation' 	=> 'The :field field is not a a.',
			'custom_validation2' 	=> 'The :field field is not a a 2.',
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

	public function custom_validation2_rule( $value, $satisfier, $field )
	{

		return false;
		
	}
}

try
{
	

	/*$fiber = new Fiber(function ($ajd): void {
		$ajd->check('ch_one', '');

		$ch = $ajd->validation_fails('ch_one');

   		$ajd2 = Fiber::suspend($ch);
   		
   		$ajd2 
   			->check('ch_two', '');
	});

	$v->required();

	$value = $fiber->start($v);

	$v 
		->required()->sometimes(function() use ($value)
		{
			return !$value;
		});


	$fiber->resume($v);*/
	$v->trigger('add');	

	$v
		->required()
			->on('add')
			->publishFail('test_required', function($event, $closure, $ajd, $value = null, $field = null)
			{
				echo 'failed required field:'.$field;
			})
		->digit()
			->on('edit')
		->check('as_evemt', '')
			->passed(function()
			{
				/*echo '<pre>';
				var_dump(func_get_args());*/
				echo 'passed';
			})

			->fails(function()
			{
				// var_dump(func_get_args());
				echo 'falied 1';
			})
			->fails(function()
			{
				// var_dump(func_get_args());
				echo 'falied 2';
			});



	$validator = $v->getValidator();

	$at = true;
	$bt = true;
	$ct = false;
	$dt = false;
	var_dump($at and $bt xor $ct xor $dt);


	$v
		->enum(Sstatus::class)
		->checkArr('enum.enums.1', 
			[
				'enum' => [
					'enums' => [
						'b',
						's'
					]
				]
			]
		);

	// example on how to use method chaining
	/*$v->required()
		->minlength(100)
		->check('username', '');

	$v->required()
		->minlength(100)
		->check('fname', '');*/

	$v
		->folder_custom()
		->folder_custom2()

		->check('folder_custom', '');


/*	$validator = $v->getValidator();

	var_dump($validator->folder_custom2()->validate('folder_custom2'));*/

	// Another way of defining validation rules
	$v
		->Srequired()
			->field('username')
				->publishFail('supper_test', function()
				{
					echo '<pre>';
					echo 'super field test required only.';
				})
				->minlength(100)
			->field('fname')
				->publishFail('supper_minelen_test', function()
				{
					echo '<pre>';
					echo 'super field test minlength.';
				})
				->minlength(1)
					->publishFail('minelen_test', function()
					{
						echo '<pre>';
						echo 'minlength test.';
					})
					->publishFail('minlengthtest2', function()
					{
						echo '<pre>';
						echo 'super field test minlength 2.';
					})
		->eSrequired()
		->checkGroup([
			'username' => '',
			'fname' => ''
		]);

		$v 
			->required(function($value)
			{
				// var_dump($value);
				return false;
			})
			->check('callback_funct', 'a');

	// if both field has the same minumum lenght requirement
	/*$v
		->Srequired()
			->Sminlength(100)
				->field('username')
				->field('fname')
			->eSminlength()
		->eSrequired()
		->checkGroup($_POST);*/

	$v->oRis_bool()
		->digit()
		->check('or_rule', '');

	$v->when()

			->Givrequired()
				->publishFail('test_on_giv_when', function()
				{
					echo '<pre>';
					echo 'test_on_giv_when';
				})

			->Givdigit()
			
		->endgiven('alles', '')

			->Givrequired()
		->endgiven('a21', '', AJD_validation::LOG_OR)

			->Givrequired()
			->Givminlength(1)
		->endgiven('a221', 'a', AJD_validation::LOG_XOR)

			->Threquired()
				->publishFail('test_on_then_when', function()
				{
					echo '<pre>';
					echo 'test_on_then_when';
				})
		->endthen('then', '')

			->Othrequired()
				->publishFail('test_on_otherwhise_when', function()
				{
					echo '<pre>';
					echo 'test_on_otherwhise_when';
				})
		->endotherwise('otherwise', '')

	->endwhen();

	$arrch = ['aass' => [1,1]];

	$v
		->distinct($arrch)
		->check('aass', $arrch);

	$v->Ftest_custom()
		->required()
		->alpha()
		->test_custom()
		->check('aa|Al', '');

	// using filters 
	// there must be a prefix (F) on the filter name
	// this will apply the define the filter after it runs the validation

	$v->Ftest()
		->required()
		->maxlength(2)
		->check('username3', 'aa');

	// if you want to run the filter before validating
	$v->Ftest(NULL, TRUE)
		->required()
		->maxlength(2)
		->check('usernameaa2', '');

	// this will override the default error message for the rule
	$v->minlength('2', '@custom_error_Custom error message runtime')
		->check('username1', 'aa');

	// if you want to get the unfiltered values after validating
	$v->get_values();

	// if you want to get the filtered values after validating
	// var_dump($v->filter_value());

	// if you want to check if a validation fails for a specific field
	// var_dump($v->validation_fails('username1')); // will return true/false

	// if you want to register a custom validation you have 4 ways

	// Registering a custom function can also register php function that returns true or false
	
	/*$v->registerFunction('is_numeric', NULL, FALSE, TRUE);
	$v->add_rule_msg('is_numeric', 'this value is not numeric');*/

	// to use the function 
	$v->required()
		->is_numeric()
		->check('amount', '1');

	$v
		->email()
		->check('email_check', '');

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
	$v->my_custom_func()->check('my_custom_func', '');

	// Registering a Method 
	$custom_method 	= new Custom_method_rule;
	$v->registerMethod('custom_method', $custom_method);
	$v->add_rule_msg('custom_method', 'this value custom method is not a');
	$v->custom_method()->check('custom_method', '');

	// Registering a Custom class
	// $path 	= dirname();
	$v->registerClass('custom_class', new Custom_class_rule);
	$v->add_rule_msg('custom_class', 'this value is not custom class a');
	$v->custom_class()->check('custom_class', '');


	// Registering an extension
	$extension 	= new Custom_extension;
	$v->registerExtension($extension);
	$v->custom_validation()->custom_validation2()->check('custom_extension', '');

	// Adding macros for reusable set of rules
	$v->setMacro('test_macro', function( $ajd )
	{
		$ajd
			->required()
			->minlength(2)
			->maxlength(30);
	});

	$v->macro('test_macro')->check('macro', '');

	// Or you can use this syntax

	$v->storeConstraintTo('group1')
			->Ftest( array(), TRUE )
			  ->required()
				->maxlength(30)
		->endstoreConstraintTo();

	$v->useContraintStorage('group1')->check('storage1', 'e');
	$v->useContraintStorage('group1')->check('storage2', '');
	var_dump($v->pre_filter_value());
	
	
	// using middleware for conditional validation
	$v->setMiddleWare('test_middleware', function( $ajd, $func, $args )
	{
		$ajd2 	= $ajd->getValidator();
	
		$ch  = $ajd2->required()->validate('a');
		
		if( $ch )
		{
			$func($ajd, $args);
		}
	});

	$v->required()
		->minlength(2)
		->middleware('test_middleware','asex', '');

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
		->check('sometimes2', '');	
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

// there must be a suffix _rule to the class name to avoid class conflict
class Custom_class_rule
{
	// must have a method run
	public function run( $value = null, $satisfier = null, $field = null )
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
	public function custom_method_rule( $value = null, $satisfier = null, $field = null )
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