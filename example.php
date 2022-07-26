<meta http-equiv="Cache-control" content="no-cache">

<?php

require 'vendor/autoload.php';

use AJD_validation\Contracts\Base_extension;
use AJD_validation\AJD_validation;
use AJD_validation\Async\Async;
use AJD_validation\Constants\Lang;
use AJD_validation\Contracts\Abstract_exceptions;
use AJD_validation\Contracts\Abstract_anonymous_rule;
use AJD_validation\Contracts\Abstract_anonymous_rule_exception;

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

$v
	->when(true)
	->addLogicClassPath(__DIR__.DIRECTORY_SEPARATOR.'CustomLogics'.DIRECTORY_SEPARATOR)
	->addLogicNamespace('CustomLogics\\')
	->endwhen();


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

	public function getAnonClass()
	{
		return [
			new class() extends Abstract_anonymous_rule
			{
				public function __invoke($value, $satisfier = NULL, $field = NULL)
				{
					return in_array($value, $satisfier);
				}

				public static function getAnonName() : string
				{
					return 'ext1_anontest';
				}

				public static function getAnonExceptionMessage(Abstract_exceptions $exceptionObj)
				{
					$exceptionObj::$defaultMessages 	= array(
						 $exceptionObj::ERR_DEFAULT 			=> array(
						 	$exceptionObj::STANDARD 			=> 'The :field field is ext1_anontest',
						 ),
						  $exceptionObj::ERR_NEGATIVE 		=> array(
				            $exceptionObj::STANDARD 			=> 'The :field field is not ext1_anontest.',
				        )
					);
				}
			},
			new class() extends Abstract_anonymous_rule
			{
				public function __invoke($value, $satisfier = NULL, $field = NULL)
				{
					return in_array($value, $satisfier);
				}

				public static function getAnonName() : string
				{
					return 'ext2_anontest';
				}

				public static function getAnonExceptionMessage(Abstract_exceptions $exceptionObj)
				{
					$exceptionObj::$defaultMessages 	= array(
						 $exceptionObj::ERR_DEFAULT 			=> array(
						 	$exceptionObj::STANDARD 			=> 'The :field field is ext2_anontest',
						 ),
						  $exceptionObj::ERR_NEGATIVE 		=> array(
				            $exceptionObj::STANDARD 			=> 'The :field field is not ext2_anontest.',
				        )
					);

					$exceptionObj::$localizeMessage 	= [
						Lang::FIL => [
							$exceptionObj::ERR_DEFAULT 			=> array(
							 	$exceptionObj::STANDARD 			=> 'The :field field ay ext2_anontest',
							 ),
							  $exceptionObj::ERR_NEGATIVE 		=> array(
					            $exceptionObj::STANDARD 			=> 'The :field field ay hindi ext2_anontest.',
					        ),
						]
					];
				}
			}
		];
	}

	public function getLogics()
	{
		return [
			'custom_logics_logic'
		];
	}

	public function custom_logics_logic($value = null, ...$satisfier) : bool
	{
		
		return $value == $satisfier[0];
	}
}

try
{
	/*
		Make anonymous class register function and extension anonymous class
	*/

	// $v->setLang(LANG::FIL);

	$v->registerAnonClass(

		new class() extends Abstract_anonymous_rule
		{
			public function __invoke($value, $satisfier = NULL, $field = NULL)
			{
				return in_array($value, $satisfier);

			}

			public static function getAnonName() : string
			{

				return 'anontest';
			}

			public static function getAnonExceptionMessage(Abstract_exceptions $exceptionObj)
			{
				$exceptionObj::$defaultMessages 	= array(
					 $exceptionObj::ERR_DEFAULT 			=> array(
					 	$exceptionObj::STANDARD 			=> 'The :field field is anontest',
					 ),
					  $exceptionObj::ERR_NEGATIVE 		=> array(
			            $exceptionObj::STANDARD 			=> 'The :field field is not anontest.',
			        )
				);

				$exceptionObj::$localizeMessage 	= [
					Lang::FIL => [
						$exceptionObj::ERR_DEFAULT 			=> array(
						 	$exceptionObj::STANDARD 			=> 'The :field field ay anontest',
						 ),
						  $exceptionObj::ERR_NEGATIVE 		=> array(
				            $exceptionObj::STANDARD 			=> 'The :field field ay hindi anontest.',
				        ),
					]
				];
			}
		}
	);


	$v
	->invokable_required()
	->check('invokeme', '');

	$v
	->anontest(3, 'a')
	->check('anontest1', '3');

	$v->anontest(5)
	->check('anontest2', '3');


	/*var_dump($v
		->getValidator()
		->folder_custom()
		
		->validate('folder_custom'));*/
	

	$v
		->folder_custom()
		->folder_custom2()

		->check('folder_custom', 'b');

	

	$v->addDbConnection(
		'test', 
		[
			'mysql:host=127.0.0.1;port=3306;dbname=dti_1bps',
			'root',
			'default'
		]
	);

	$ch_db = $v->exists('test|table=requests', $v->LgDb_example()->Lgfirst(true)->wrapLogic()
	)

	// ->validate(5);

	->check('db_ch', 4)
	;

	/*$v
	->when()
			->Lgfirst(false)
		->endgiven('lgonlyfield1')

			->Threquired()
		->endthen('thenprintthis1')

			->Othrequired()
		->endotherwise('elseprintthis')
	->endwhen();*/

	// var_dump($ch_db);

	$arr1 = ['password' => '1', 'password_confirm' => '1'];
	$arr2 = ['password' => 'ssa', 'password_confirm' => 'ssa'];
	/*$obj = new StdClass;

	$obj->foo = '1';
	$obj->bar = '1';*/
	// $arr2 = ['key_value_field' => ['key_value_field' => 'ss', 'confirm' => 'ssa']];
	
	/*$v->dimensions(['ratio' => 0.0007])->check('dimensions_field', '/home/asiagate/Pictures/decision.png'); 
	var_dump($v->getValidator()->dimensions(['ratio' => 0.0007])->validate('/home/asiagate/Pictures/decision.png'));*/
/*	AJD_validation::makeAsync()
			->required()
			->check('aaasera')->getFiber();*/


	/*$v 
		->dependent(
			'dependent_field', $v->getValidator()->digit(),
			$v->getValidator()->email()
		)
		->checkDependent('real_field', $dependent_arr, $dependent_arr);*/

	$dependent_arr = ['dependent_field' => 'a', 'd2' => '', 'real_field' => ''];
	$dependent_field = ['dependent_field', 'd2'];
	$dependent_values = ['dependent_field' => 's', 'd2' => 'a'];

	$v 
		->required_without_all_message(
			$dependent_field, $dependent_values, $dependent_arr
		)
		->checkDependent('real_field', $dependent_arr)
		->then(function()
		{
			echo "dependent success";
		},
		function()
		{
			echo "dependent fails";	
		});

	$dependent_arr1 = [
		'dependent_field' => '',
		'dependent_field2' => '1',
		'check_dependent_field' => ''
	];

	$dependent_field1 = [
		'dependent_field', 'dependent_field2'
	];

	$dependent_values1 = [];

	$checkValidator1 = $v->getValidator()->required()->digit();

	$fieldValidator1 = $v->getValidator()->required()->alpha();

	$v 
		->dependent_all(
			$dependent_field1, 
			$checkValidator1,
			$fieldValidator1,
			$dependent_values1, 
			$dependent_arr1
		)
		->checkDependent('check_dependent_field', $dependent_arr1);

	$v 
		->one_or([$v->getValidator()->digit(), $v->getValidator()->email()])
		->check('none_field', 'a@test.coma');

		
	$v 
		->mime_type('image/png')
		->check('mime_type_field', '/home/asiagate/Pictures/decision.png');

	/*var_dump(
		$v 
			->getValidator()
			->mime_type('image/png')
			->validate('/home/asiagate/Pictures/decision.png')
	);*/

	$v
		->subdivision_code('PH')
		->check('Ph_subdiv', '22CASa');



	Async::when(
		AJD_validation::makeAsync()
			->required()
			->check('aaasera'),

		AJD_validation::makeAsync()
			->required()
			->check('aaasera2')
	)->promise()
	
	->then(function()
	{
		echo 'aa';

		return 1;
	}, function()
	{
		echo 'error';
	})
	->then(function($a)
	{

		echo $a + 1;
		return $a + 1;
	})
	->catch(function($e)
	{
		var_dump($e->getMessage());
	})
	;

	Async::when(
		AJD_validation::makeAsync()
			->required()
			->check('aaasera_me1', '1'),

		AJD_validation::makeAsync()
			->required()
			->check('aaasera_me2', '1'),

		AJD_validation::makeAsync()
			->required()
			->check('aaasera_me3', '1')
	)->promise()
	->fails(function()
	{
		echo 'failed async aa2';
	})
	->passed(function()
	{
		echo 'passed async aa a2';
	})
	->then(function()
	{
		echo 'aa sex 2';

		return 1;
	}, function()
	{
		echo 'error 2';
	})

	->catch(function($e)
	{
		var_dump($e->getMessage());
	});


/*
	$fiber = new Fiber(function ($ajd): void {
		$ajd->check('ch_one', '');

		$ch = $ajd->validation_fails('ch_one');

   		$ajd2 = Fiber::suspend($ch);
   		
   		$ajd2 
   			->check('ch_two', '');

   			Fiber::suspend('aaaas');
	});

	$v->required();

	$value = $fiber->start($v);

	$v 
		->required()->sometimes(function() use ($value)
		{
			return !$value;
		});*/



	$v->trigger('add');	
	// $v->setGlobalFiberize(true);

	/*$main = new \Fiber(function() use($v){
		
	});*/

	

	$v
		->required()
		->minlength(2)
		->checkAsync('aeee', 'a')
			->fails(function($ajd)
			{
				/*$ajd->required()
					->check('aaee_fail_event');*/

				echo 'falied 1 aeee';
			})
			->passed(function($ajd)
			{
				/*$ajd->required()
					->check('aaee_pass_event');*/
			})
			->then(function()
			{
				
				echo 'aaa aeee success';
			},
			function()
			{
				echo '<br/> error on aeee 1 <br/>';
			})
			->catch(function($e)
			{

				return $e->getMessage();
			})
			->then(function($a)
			{
				var_dump($a) ;
			});
			

			

	$v
		->fiberize()
		->required()
			->suspend()
			->publishFail('test_required', function($event, $closure, $ajd, $value = null, $field = null)
			{
				/*echo '<pre>';
				print_r(func_get_args());*/
				echo 'failed required field:'.$field;
			})
		->digit()
			->suspend()
		->minlength(1)

		
		->check('as_evemt', '')
			->fiber(function($ajd, $fiber, $field, $rule, $val)
			{	
				echo '<pre>';
				var_dump($field);
				var_dump($rule);
				$fiber->resume(function() use($rule)
				{
					echo $rule;
				});

			})
			->fiber(function($ajd, $fiber, $field, $rule, $val)
			{	
				/*echo '<pre>';
				var_dump($field);
				var_dump($rule);
				$fiber->resume($rule);*/

				// echo 'as event fiber 2';
			})

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
			})
			->then(function()
			{
				echo 'eerthen';
			});

		/*Async::await(

			$v
				->makeAsync()
				->required()
				->minlength(1)
				->check('async1', '1')
		);

	Async::await(

			$v
				->makeAsync()
				->required()
				->minlength(2)
				->digit()
				->check('async2', '2')
		);

	Async::await(

			$v
				->makeAsync()
				->required()
				
				->check('async3', '')
		);

	// $main->start();

	Async::run()->then(function()
		{
			AJD_validation::required()
			->check('thenasyncsuccess');
		},
		function()
		{
			AJD_validation::required()
			->check('thenasyncfails');	
		}

	);*/
	

	$validator = $v->getValidator();

	$at = true;
	$bt = true;
	$ct = false;
	$dt = false;
	var_dump($at and $bt xor $ct xor $dt);

	$v
		->fiberize()
		->enum(Sstatus::class)
			->suspend()
		->checkArr('enum.enums', 
			[
				'enum' => [
					'enums' => [
						'b' => Sstatus::DRAFT,
						'a' => ''
					]
				]
			]
		)
		->fiber(function($ajd, $fiber)
		{
			// $fiber->resume('a');
			echo 'fiber2';
		})
		->passed(function()
		{
			echo 'enums passed';
		})
		->fails(function()
		{
			echo 'enums fails';
		})
		->then(function()
		{
			echo 'enum passed then';
		},
		function($e)
		{
			echo $e->getMessage();
		})
		
		;


	// example on how to use method chaining
	/*$v->required()
		->minlength(100)
		->check('username', '');

	$v->required()
		->minlength(100)
		->check('fname', '');*/

		/*$v 
			->required()->on('add')
			->minlength(1)
			->check('middlename');*/

/*	$validator = $v->getValidator();

	var_dump($validator->folder_custom2()->validate('folder_custom2'));*/

	$v 
		->required()
		->minlength(3)->sometimes($v->getValidator()->required_allowed_zero()->digit())
		->check('sometimes_new', '000');

	$v

		->Srequired()
			->field('username2')->sometimes($v->Lgfirst(true)->wrapLogic())
				->minlength(2)
				->alpha()
			->field('fname2')
				->minlength(1)->sometimes(function($value = null, $satisfier = null, $field)
				{
					return true;
				})
		->eSrequired()

		->checkGroup([
			'username2' => '',
			'fname2' => '',
			
		]);

		$v 
			->minlength(2)
			->digit()
			->compare('==', 'b', '@custom_error_"b" is the value for middlename2 to be accpted.')
			->check('middlename2', 'aa');
	
	// Another way of defining validation rules
	$v
		->Srequired(NULL, AJD_validation::LOG_AND)
			->field('username')
				->publishFail('supper_test', function()
				{
					echo '<pre>';
					echo 'super field test required only.';
				})
				->minlength(2)
				->alpha()->sometimes('sometimes')
			->field('fname')
				->publishFail('supper_minelen_test', function()
				{
					echo '<pre>';
					echo 'super field test minlength.';
				})
				->minlength(1)->on('add')
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
		->Sdigit(NULL, AJD_validation::LOG_AND)
			->field('digit_group')
			->field('digit_group2')
		->eSdigit()
		->checkGroup([
			'username' => ['username' => ['aa', '']],
			'fname' => ['fname' => ['', 'a']],
			/*'username' => 'a',
			'fname' => '',*/
			'digit_group' => '1',
			'digit_group2' => ''
		])
		->then(function()
		{
			echo 'group passed';
		}, function()
		{
			echo 'group failed';
		});

		$v 
			->required(function($value)
			{
				// var_dump($value);
				return false;
			})
			->check('callback_funct', ['callback_funct' =>  ['', ''] ]);

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

	$v
		->when()
			// ->Givfiberize()
			->Givrequired()
				->publishFail('test_on_giv_when', function()
				{
					echo '<pre>';
					echo 'test_on_giv_when';
				})
				->suspend()

			->Givdigit()
			
		->endgiven('alles', null)


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

	->endwhen()

		->fiber(function($ajd, $fiber, $field)
				{
					$fiber->resume('from when');
					var_dump($field);
					echo 'when aaaaa';
				})
		;

	$arrch = ['aass' => [1,'1']];

	$v
		->distinct($arrch)
		->check('aass', $arrch);
		var_dump('distinct');
	var_dump($v->getValidator()->distinct()->validate([1,'2']));

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
		->check('amount', '');

	$v
		->email()
		->check('email_check', '');

	$v
		->required()
		->is_array()
		->check('is_array_field', '', false);

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
	$v->my_custom_func()->check('my_custom_func', 'b');

	// Registering a Method 
	$custom_method 	= new Custom_method;
	$v->registerMethod('custom_method', $custom_method);
	$v->add_rule_msg('custom_method', 'this value custom method is not a');
	$v->custom_method()->check('custom_method', '');

	// Registering a Custom class
	// $path 	= dirname();
	$v->registerClass('custom_class', new Custom_class);
	$v->add_rule_msg('custom_class', 'this value is not custom class a');
	$v->custom_class()->check('custom_class', '');


	// Registering an extension
	$extension 	= new Custom_extension;
	$v->registerExtension($extension);
	$v->custom_validation()->custom_validation2()->check('custom_extension', '');

	// $v->Lgcustom_logics(5)->runLogics('5');

	$v->ext1_anontest(3)
	->check('ext1_anontest', '1');

	$v->ext2_anontest(3)
	->check('ext2_anontest', '1');

	// Adding macros for reusable set of rules
	$v->setMacro('test_macro', function( $ajd )
	{
		$ajd
			->required()
			->minlength(2)
			->maxlength(30);
	});

	$v->macro('test_macro')->check('macro', '')
	;

	// Or you can use this syntax

	$v->storeConstraintTo('group1')
			// ->Ftest( array(), true )
			  ->required()
			  ->maxlength(30)
		->endstoreConstraintTo();

	$v->storeConstraintTo('group2')
		// ->Ftest( array(), true )
		  ->required()
		  ->minlength(2)
	->endstoreConstraintTo();


	$v->useContraintStorage('group1')->check('storage1', '');

	$v->useContraintStorage('group2')->alpha()->check('storage2', '')
	;

	$v->useContraintStorage('group1')->digit()->check('storage3', '')
	;


	var_dump($v->pre_filter_value());
	
	$v->setMiddleWare('test_middleware2', function( $ajd, $func, $args )
	{
		// echo 'middleware 2';
		
		return $func($ajd, $args);
		
	});
	
	// using middleware for conditional validation
	$v->setMiddleWare('test_middleware', function( $ajd, $func, $args )
	{
		// echo 'middleware 1';
		$ajd2 	= $ajd->getValidator();
	
		$ch  = $ajd2->required()->validate('a');
		
		if( $ch )
		{
			return $func($ajd, $args);
		}
	});

	

	$v->required()
		->minlength(2)
		->middleware('test_middleware2','asex2', '');

	$v->required()
		->minlength(2)
		->middleware('test_middleware','asex', '')
		
		/*->fails(function()
		{
			echo 'middleware fails';
		})
		
		->passed(function()
		{
			echo 'middleware passed';
		})*/
		;

	$v
		->required()
		->checkAllMiddleware('all_middleware', '')
		->fails(function()
		{
			echo 'all middleware fails';
		})
		
		->passed(function()
		{
			echo 'all middleware passed';
		})
		->otherwise(function($e)
		{
			echo $e->getMessage();
		});

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
class Custom_class
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

class Custom_method
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