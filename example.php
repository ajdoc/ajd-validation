<meta http-equiv="Cache-control" content="no-cache">

<?php

/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/

require 'vendor/autoload.php';

use AJD_validation\Contracts\Base_extension;
use AJD_validation\AJD_validation;
use AJD_validation\Async\Async;
use AJD_validation\Constants\Lang;
use AJD_validation\Contracts\Abstract_exceptions;
use AJD_validation\Contracts\Abstract_anonymous_rule;
use AJD_validation\Contracts\Abstract_anonymous_rule_exception;
use AJD_validation\Contracts\CanMacroInterface;

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
	->addFilterDirectory(__DIR__.DIRECTORY_SEPARATOR.'CustomFilters'.DIRECTORY_SEPARATOR)
	->addFilterNamespace('CustomFilters\\');

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
			'custom_validation',
			'custom_validation2'
		);
	}

	public function getRuleMessages()
	{
		return array(
			'custom_validation' 	=> 'The :field field is not a a.',
			'custom_validation2' 	=> 'The :field field is not a a 2.',
		);
	}

	public function custom_validation( $value, $satisfier, $field )
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

	public function custom_validation2( $value, $satisfier, $field )
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

	/*
		Adding custom filters
	*/
	public function getFilters()
	{
		return [
			'custom_string_filter',
		];
	}

	/*
		Adding custom macros
	*/
	public function getMacros()
	{
		return [
			'extension_macro',
			'extension_macro2'
		];
	}

	/*
		filter method must always have _filter suffix
	*/
	public function custom_string_filter( $value, $satisfier, $field )
	{
		$value 	= filter_var( $value, FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_NO_ENCODE_QUOTES ).'_from_extension';

		return $value;
	}


	public function extension_macro()
	{
		return function()
		{
			$this->required()
				->minlength(7);

			return $this;
		};
	}

	public function extension_macro2($args = null)
	{
		return function($args = null)
		{
			if($args)
			{
				$this->setArguments([$args]);
			}


			$this->registerAsRule(function($value, $satisfier = null)
			{
				if (!is_numeric($value)) 
		        {
		            return false;
		        }

		        return $value > 0;

				
			}, ['default' => 'Value :field must be positive ext :*', 'inverse' => 'Value :field must not be positive ext :*']);

			return $this;
		};
	}
}

$testmacroPositive = function($args = null)
{
	$rules = [
		'required',
		'minlength'
	];

	/*foreach($rules as $rule)
	{
		$ruleName = $rule;

		if($this::getInverse())
		{
			$ruleName = 'Not'.$rule;
		}

		if($rule == 'minlength')
		{
			$this->{$ruleName}(2);	
		}
		else
		{
			$this->{$ruleName}();
		}
		
	}*/

	if($args)
	{
		$this->setArguments([$args]);

		/*$this->setArguments([$args], 'positive1');
		$this->setArguments([$args], 'positive2');*/
	}


	$this->registerAsRule(function($value, $satisfier = null)
	{
		if (!is_numeric($value)) 
        {
            return false;
        }

        return $value > 0;

		
	}, ['default' => 'Value :field must be positive :*', 'inverse' => 'Value :field must not be positive :*']);

	/*$this->registerAsRule(function($value, $satisfier = null)
	{
		// echo '<pre>';
		// var_dump($satisfier);

		if (!is_numeric($value)) 
        {
            return false;
        }

        return $value > 0;

		
	}, ['default' => 'Value :field must be positive :*', 'inverse' => 'Value :field must not be positive :*'], 'positive1');

	$this->registerAsRule(function($value, $satisfier = null)
	{
		// echo '<pre>';
		// var_dump($satisfier);

		if (!is_numeric($value)) 
        {
            return false;
        }

        return $value > 0;

		
	}, ['default' => 'Value :field must be positive2 :*', 'inverse' => 'Value :field must not be positive2 :*'], 'positive2');*/


	return $this;
};

class Custom_macro implements CanMacroInterface
{
	protected $testmacroPositive;
	public $testarg;

	public function __construct($testmacroPositive = null, $testarg = null)
	{		
		$this->testmacroPositive = $testmacroPositive;
		$this->testarg = $testarg;
	}

	public function getMacros() 
	{
		return [
			'positive3',
			'mymacro_class'
		];
	}

	public function positive3()
	{
		return $this->testmacroPositive;
	}

	public function mymacro_class()
	{
		$that = $this;
		return function($minlength = 2) use ($that)
		{
			echo $that->testarg;

			$this
				->required()
				->minlength($minlength)
				;

			return $this;
		};
	}
}

/*try
{
	$v->addPackages([
		PackageAjd\PackageValidationServiceProvider::class
	]);

	$v->setValidation('packagevalidation');

	$v 
		->required()
		->minlength(2)
		// ->useValidation(\PackageAjd\Validations\PackageValidation::class)
		// ->useValidation('packagevalidation')
		// ->customAction()
		->check('packagevalidation')
		->customAction()
		->getPromise()
		->otherwise(function()
		{
			echo 'failed';
		})
		;

	$v->assert();
}
catch(Exception $e)
{
	echo $e->getMessage();
}
die;*/

try
{
	$v->addPackages([
		PackageAjd\PackageValidationServiceProvider::class
	]);
	/*
		Make anonymous class register function and extension anonymous class
	*/

	// $v->registerPackage(new PackageAjd\PackageAjd\PackageAjdValidatorServiceProvider);

	// $v->setLang(LANG::FIL);

	// Registering an extension
	$extension 	= new Custom_extension;
	$v->registerExtension($extension);

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

	// var_dump($v->Lgpackage(true)->runLogics(''));

	/*$v 
		// ->Fpackage(null, true)
		->required()
		->minlength(2)
		// ->useValidation(\PackageAjd\Validations\PackageValidation::class)
		->useValidation('packagevalidation')
		->customAction()
		->check('packagevalidation');*/

	// var_dump($v->pre_filter_value());


		$v->mixin(Custom_macro::class, true, $testmacroPositive, '1' );

		$v->macro('positive', $testmacroPositive);


		$v->macro('mymacro', function($minlength = 2)
		{
			$required = 'required';
			$minlengthstr = 'minlength';
			$positive = 'positive';
			$not = 'Not';

			if($this->getInverse())
			{
				$required = $not.$required;
				$minlengthstr = $not.$minlengthstr;
				$positive = $not.$positive;
			}

			$this
				->{$required}()
				->{$minlengthstr}($minlength)
				->{$positive}()
					// ->sometimes('sometimes')
				;

			return $this;
			
		});

		// $v->Notpositive3()->check('macrofield', '2');

		$v->macro('mymacro2', function($minlength, array $data)
		{
			$obj = $this->Srequired()
					->Sminlength($minlength);

			if(!empty($data))
			{
				foreach($data as $field => $val)
				{
					$obj->field($field);
				}

			}
						
			$obj->eSminlength()
				->eSrequired()
				->checkGroup($data)
				;

			return $obj;
			
		});

		$v 
			->required()
			->oRmobileno()
			->oRemail()
			->check('email_or_mobile', '9255995921');

			// $v->required()->is_bool()->check('is_bool_1', true);

		$v->macro('negative', function()
		{
			return $this->registerAsRule(function($value)
			{

				if (!is_numeric($value)) 
                {
                    return false;
                }

                return $value < 0;

			}, ['default' => 'Value :field must be negative']);
		});

		/*$v
			->extension_macro()
			->extension_macro2(2)
			
			->check('extension_macro1', '');*/

		$v->positive3(2)
		->check('register_as_rule', '')
		->then(function()
		{
			echo 'pass register as rule';
		},
		function()
		{
			echo 'fail register as rule';
		})
		;

		// var_dump($v->getValidator()->required()->positive3()->setName('test')->assertErr('a', true));

		$v->negative()
		->check('register_as_rule2', '')
		;

		$v->mymacro(2)->check('fieldmacro', '');

		/*$v->mymacro(2)->mymacro(7)->check('fieldmacro', '');

		$v->mymacro(3)->check('fieldmacro2', '');*/

		/*$macr1 = [
			'fieldmacrogroup1' => '',
			'fieldmacrogroup2' => '',
		];

		$macr2 = [
			'fieldmacrogroup3' => '',
		];

		$v->mymacro2(7, $macr1)->mymacro2(2, $macr1);

		$v->mymacro2(3, $macr2);*/
		

		$v 
	->required()
	->minlength(5)
	// ->uncompromised()
	// ->useValidation(\AJD_validation\Validations\DebugValidation::class)
	->check('custom_validation', '')
	// ->printCollectedData()
	;


	$v 
		->email(['showSubError' => false, 'useDns' => false], '#client_new_email')
		->check('new_email', 'a@');

	var_dump($v->getClientSide());
	

	$v
	->invokable_required()
	->check('invokeme', '');

	$v
	->anontest(3, 'a')
	->anontest(4, 'a')
	->check('anontest1', '1');

	$v->anontest(5)
	->check('anontest2', '3');

	/*Async::when(
		$v->required()->groups('g1')
			->minlength(2)->groups('g2')
			->useGroupings($v->createGroupSequence(['g1', 'g2']))
		->check('async_grouping', 'a'),

		$v->maxlength(3)->groups('gg1')
			->minlength(2)->groups('gg2')
			->useGroupings($v->createGroupSequence(['gg2', 'gg1']))
		->check('async_grouping2', 'aaaaa')
	)->promise();*/

	/*var_dump($v
		->getValidator()
		->folder_custom()
		
		->validate('folder_custom'));*/

		// new \AJD_validation\Helpers\Group_sequence(['t3', 't1', 't2'])


	// ['check_or1' => ['', '']

	$v 
->Srequired(null,  AJD_validation::LOG_OR)->groups('t1')
	->Sminlength(2, AJD_validation::LOG_AND)->groups('t2')
		->field('field_group1')
			->alpha()->groups('t3')
		->useGroupingsField($v->createGroupSequence(['t1', 't2', 't3']))
		->field('field_group2')
			->digit()->groups('t4')
		->useGroupingsField($v->createGroupSequence(['t1', 't2', 't4']))
	->eSminlength()
->eSrequired()
->checkGroup([
	'field_group1' => ['field_group1' => ['a', 'a']],
	'field_group2' => ['field_group2' => ['aa', 'aa']],
	
]);

	$v->any(
		$v->required()->check('group_and_single1', ''),

		$v 
			->Srequired(null, AJD_validation::LOG_OR)
				->field('group_and_single2')

				->field('group_and_single3')
					->minlength(2)
			->eSrequired()
			->checkGroup(
				[
					'group_and_single2' => '',
					'group_and_single3' => 'aa',
				]
			)
	);

	$v->any(
		$v->required()->minlength(2)->check('or_field1',['or_field1' => ['', '']]), 
		$v->required()->check('or_field2',['or_field2' => ['']]),
		$v->required()->check('or_field3','a'),
	);


	$v 
		->Srequired(null, AJD_validation::LOG_OR, '@custom_error_TestGr1')->groups('t1')
		
		
			->field('check_or1')
				// ->minlength(2, '@custom_error_Tetsma2')->groups('t4')				

			->field('check_or2')
				// ->minlength(3, '@custom_error_Tetsma1')->groups('t3')				

		->eSrequired()
		->useGroupings($v->createGroupSequence(['t1', 't4', 't3']))
	->checkGroup([
		'check_or1' => '',
		// 'check_or1' => ['check_or1' => ['aaa', 'a'] ],
		// 'check_or2' => ['check_or2' => ['', ''] ]

		'check_or2' => ''
		

	]);


	$v 
		->required(null, '@custom_error_Field is required.')->groups(['t1'])
		->minlength(3)->groups(['t1'])

		
		->maxlength(5)->groups('t2')
		->alnum(['*', '&'])->groups('t2')

		->uncompromised()->groups('t3')

		->useGroupings($v->createGroupSequence(['t1', 't2', 't3']))
		// ->check('grouping_field', 'aaaaaa');
		->check('grouping_field', ['grouping_field' => ['', '']]);

	// ['check_or1' => ['', ''] ]

	// $validatorGroup = $v->getValidator();

	$v 
		->sequential(
			$v
				->getValidator()
				->invokable_required()
				->minlength(2),
				
			$v
				->getValidator()
				->maxlength(5)
				->alpha(''),

			$v
				->getValidator()
				->uncompromised(),			
		)
		->check('sequential_field', '');

	var_dump(
	$v 
		->getValidator()
		->sequential($v->getValidator()->invokable_required()->minlength(2))
		->validate('aa'));
	
	$v 
		->password()
		->check('compound_password', '');

	/*[
		// field key
		0 => [
			'field_name' => ''
			'details' => [
				// values key
				0 => [
					// rules key
					0 => [
						'rule_name' => 'required',
						'passed' 	=> ''
					],
					1 => [
						''
					],
				],
				1 =>  [

				]
			]
		],
		// field key 2
		1 => [
			'field_name' => ''
			'details' => [
				// values key
				0 => [
					// rules key
					0 => [
						'rule_name' => 'required',
						'passed' 	=> ''
					],
					1 => [
						''
					],
				],
				1 =>  [

				]
			]
		]
		
	]*/	

	/*var_dump($v 
		->getValidator()
		->password()
		->validate('ameac'));*/


	$v
		->folder_custom()
		->folder_custom2()
		
		->check('folder_cussstom', 'b');

	$v 
		->required()
		->package_test()
		->check('package_test_field');

	/*$v->addDbConnection(
		'test', 
		[
			'mysql:host=127.0.0.1;port=3306;dbname=dti_1bps',
			'root',
			'default'
		]
	);*/

	/*$ch_db = $v->exists('test|table=requests', $v->LgDb_example()->Lgfirst(true)->wrapLogic()
	)

	// ->validate(5);

	->check('db_ch', 4)
	;*/

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

		
	/*$v 
		->mime_type('image/png')
		->check('mime_type_field', '/home/asiagate/Pictures/decision.png');*/

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

	/*$v 
		->required()
		->digit()
		->checkArr('arr.arr1.sub_arr2', [
			'arr' => [
				'arr1' => [
					'sub_arr' => 'a',
					'sub_arr2' => ['', '']
				],
				'arr2' => []
			]
		]);*/

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

	$v->oRis_bool(null, '@custom_error_isbooltest')
	->oRis_bool(null, '@custom_error_isbooltest2')
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

	$toFiler = [
		'field1' => ['1aas', '2sd'],
		'field2' => '1'
	];

	$filteredValues = $v
		->Ffilter_sanitize([FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_NO_ENCODE_QUOTES])
		->Fextract_length()
		->Fadd_aes_decrypt('test')
		
		
			->cacheFilter('field1')
		->Ffilter_sanitize(FILTER_SANITIZE_NUMBER_INT)
		
			->cacheFilter('field2')
		->filterAllValues($toFiler);

	var_dump($filteredValues);


	$filteredSingle = $v 
							->Ffilter_sanitize([FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_NO_ENCODE_QUOTES])
							->Fwhite_space_option()
							->Fadd_aes_decrypt('test')

						->cacheFilter('fieldsingle')
						->filterValue('as   ');

	var_dump($filteredSingle);

	$v->required()
		->maxlength(5)
		->check('field1', $filteredValues);

	$v
		->Fadd_aes_decrypt('test', true)
		->Fextract_length(null, true)
		
		->required()
		->maxlength(5)
		->digit()
		->check('aa|Al', 'a');

	// using filters 
	// there must be a prefix (F) on the filter name
	// this will apply the define the filter after it runs the validation

	$v
	->Ffilter_sanitize(FILTER_SANITIZE_NUMBER_INT, true)
		->required()
		->digit()
		->check('test_digit', 'aa');

	$v->Ffilter_sanitize([FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_NO_ENCODE_QUOTES])
		->required()
		->alpha()
		->check('test_string', '<<aa>>');

	$v->Ftest()
		->required()
		->maxlength(2)
		->check('username3', 'aause3');

	/*$v->Furl(null, true)->check('field', 'https://www.examp��le.co�m');
	$v->Furl()->check('field', 'https://www.example.com');*/
	

	// if you want to run the filter before validating
	$v->Ftest(NULL, TRUE)
		->required()
		->maxlength(2)
		->check('usernameaa2', 's');

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

	function test_inline($value)
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

	// custom function using callback/Closure
	$v->registerFunction('my_custom_func', function($value)
	{
		if( $value == 'a' )
		{
			return true;
		}
		else
		{
			return false;
		}

	}, ['default' => 'this value is not a', 'inverse' => 'not this value is not a']);


	// $v->registerFunction('test_inline', null, ['default' => 'this value is not a']);

	// $v->add_rule_msg('my_custom_func', 'this value is not a');
	// var_dump($v->getValidator()->my_custom_func()->assertErr('b'));
	$v->my_custom_func()->check('my_custom_func', 'b');
	
	/*$v->test_inline()->check('my_custom_func', 'b');
	var_dump($v->getValidator()->test_inline()->validate('b'));
	var_dump('test_func_register');*/
	
	// Registering a Method 
	$custom_method 	= new Custom_method;
	$v->registerMethod('custom_method', $custom_method, ['default' => 'this value custom method is not a', 'inverse' => 'not this value custom method is not a'], ['custom_args']);
	// $v->add_rule_msg('custom_method', 'this value custom method is not a');
	/*var_dump($v->getValidator()->custom_method()->assertErr('b'));
	var_dump('test_func_register');*/
	$v->custom_method()->check('custom_method', '');


	// Registering a Custom class
	// $path 	= dirname();
	$v->registerClass( new Custom_class, ['default' => 'this value is not custom class a', 'inverse' => 'not this value is not custom class a']);
	// var_dump($v->getValidator()->custom_class()->validate('a'));
	$v->custom_class()->check('custom_class', '');


	
	$v->custom_validation()->custom_validation2()->check('custom_extension', '');

	// $v->Lgcustom_logics(5)->runLogics('5');

	$v->ext1_anontest(3)
	->check('ext1_anontest', '1');

	$v->ext2_anontest(3)
	->check('ext2_anontest', '1');

	// Adding macros for reusable set of rules
	/*$v->setMacro('test_macro', function( $ajd )
	{
		$ajd
			->required()
			->minlength(4)
			->maxlength(30);
	});

	$v->macro('test_macro')->check('macro', '');*/

	// Or you can use this syntax

	$v->storeConstraintTo('group1')
			->Ftest( array(), true )
			->Fadd_aes_decrypt('aa', true)

				->cacheFilter('group1')
			  ->required()
			  
			  ->maxlength(30)
		->endstoreConstraintTo();

	$v->storeConstraintTo('group2')
		// ->Ftest( array(), true )
		  ->required()
		  ->minlength(2)
		  
	->endstoreConstraintTo();


	$v->useConstraintStorage('group1')->check('storage1', ['storage1' => ['s', 'exx']]);

	$v->useConstraintStorage('group2')->alpha()->check('storage2', '')
	;

	$v->useConstraintStorage('group1')->digit()->check('storage3', '')
	;

	var_dump($v->pre_filter_value());
	var_dump($v->filter_value());
	
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

	$v
		->required()
		->checkAllMiddleware('all_middleware2', '');

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
catch( Abstract_exceptions $e )
{
	var_dump($e->getFullMessage());
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
	public function custom_method( $value = null, $satisfier = null, $field = null )
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