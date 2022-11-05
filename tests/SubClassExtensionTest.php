<?php 
use AJD_validation\AJD_validation;
use AJD_validation\Contracts\{
	Base_extension, Abstract_anonymous_rule_exception, Abstract_anonymous_rule,
	Abstract_exceptions
};

use AJD_validation\Constants\Lang;

class TestExtension extends Base_extension
{
	public function getName()
	{
		return self::class;
	}

	public function new_test_rule($value, $satisfier, $field)
	{
		AJD_validation::addRuleMessage('new_test_rule', ['default' => 'Field :field must be valid.', 'inverse' => 'Field :field must not be valid.']);

		return $value == $satisfier[0];
	}

	public function getRules()
	{
		return [
			'custom_validation',
			'custom_validation2'
		];
	}

	public function getRuleMessages()
	{
		return [
			'custom_validation' 	=> ['default' => 'The :field field must be a valid custom_validation.', 'inverse' => 'The :field field must not be a valid custom_validation.'],
			'custom_validation2' 	=> 'The :field field must be a valid custom_validation2.',
		];
	}

	public function custom_validation( $value, $satisfier, $field )
	{
		if( $value == 'a' )
		{
			return true;
		}
		
		return false;
		
	}

	public function custom_validation2( $value, $satisfier, $field )
	{
		if( $value == $satisfier[0] )
		{
			return true;
		}
		
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
					$exceptionObj::$defaultMessages = [
						 $exceptionObj::ERR_DEFAULT => [
						 	$exceptionObj::STANDARD => 'The :field field must be a valid ext1_anontest.',
						 ],
						 $exceptionObj::ERR_NEGATIVE => [
				            $exceptionObj::STANDARD => 'The :field field must not be a valid ext1_anontest.',
				        ]
					];
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
					$exceptionObj::$defaultMessages = [
						 $exceptionObj::ERR_DEFAULT => [
						 	$exceptionObj::STANDARD => 'The :field field must be a valid ext2_anontest.',
						 ],
						 $exceptionObj::ERR_NEGATIVE => [
				            $exceptionObj::STANDARD => 'The :field field must not be a valid ext2_anontest.',
				        ]
					];

					$exceptionObj::$localizeMessage = [
						Lang::FIL => [
							$exceptionObj::ERR_DEFAULT => [
							 	$exceptionObj::STANDARD => 'The :field field ay dapat na tama na ext2_anontest.',
							 ],
							  $exceptionObj::ERR_NEGATIVE => [
					            $exceptionObj::STANDARD => 'The :field field ay dapat hindi na tama na ext2_anontest.',
					        ],
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
		filter method must always have _filter suffix
	*/
	public function custom_string_filter( $value, $satisfier, $field )
	{
		$value 	= filter_var( $value, FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_NO_ENCODE_QUOTES ).'_from_extension';

		return $value;
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

				
			}, ['default' => 'Value :field must be positive extentsion :*', 'inverse' => 'Value :field must not be positive extentsion :*']);

			return $this;
		};
	}

	public function getClientSides()
	{
		return [
			'custom_validation' => [
				'clientSide' => function(string $field, string $rule, $satisfier = null, string $error = null, $value = null)
				{
					$js[$field][$rule]['rule'] = <<<JS
						data-parsley-$rule="emailaass"
JS;

					$js[$field][$rule]['message'] = <<<JS
						data-parsley-$rule-message="$error"
JS;

					return $js;
				}
			]
		];
	}
}

class SubClass extends AJD_validation
{
	public function sub_class_rule($value, $satisfier, $field)
	{
		static::addRuleMessage('sub_class_rule', ['default' => 'Field :field of sub class rule must be valid.', 'inverse' => 'Field :field of sub class rule must not be valid.']);
		
		return $value == $satisfier[0];
	}
}

AJD_validation::registerExtension(new TestExtension);

test('extension_object_method_rule_passed', function()
{
	testAjd(function($v)
    {
		$v->new_test(1)->check('new_test_rule', 1);

		$v->new_test(2)->check('new_test_rule2', 2);

		testAjdErrorMatcher($v, '');
	});
	
});

test('extension_object_method_rule_fails', function()
{
	testAjd(function($v)
    {

		$v->new_test(1)->check('new_test_rule3');

		$v->new_test(2)->check('new_test_rule5');

		testAjdErrorMatcher($v, 'Field New test rule3 must be valid.</br>Field New test rule5 must be valid.</br>');
	});
	
});

test('extension_object_method_rule_inversed_passed', function()
{
	testAjd(function($v)
    {
		$v->Notnew_test(1)->check('new_test_rule6', '');

		$v->Notnew_test(2)->check('new_test_rule7', '');

		testAjdErrorMatcher($v, '');
	});
	
});

test('extension_object_method_rule_inversed_fails', function()
{
	testAjd(function($v)
    {
		$v->Notnew_test(1)->check('new_test_rule8', 1);

		$v->Notnew_test(2)->check('new_test_rule9', 2);

		testAjdErrorMatcher($v, 'Field New test rule8 must not be valid.</br>Field New test rule9 must not be valid.</br>');
	});
	
});

test('extension_rule_passed', function()
{
	testAjd(function($v)
    {
		$v->custom_validation()->custom_validation2('a')->new_test('a')->check('ext_rule', 'a');

		AJD_validation::custom_validation()->custom_validation2('a')->new_test('a')->check('ext_rule2', 'a');

		testAjdErrorMatcher($v, '');
	});
	
});

test('extension_rule_fails', function()
{
	testAjd(function($v)
    {
		$v->custom_validation()->custom_validation2('a')->new_test('a')->check('ext_rule3', '');

		AJD_validation::custom_validation()->custom_validation2('a')->new_test('a')->check('ext_rule5', '');

		testAjdErrorMatcher($v, 'The Ext rule3 field must be a valid custom_validation.</br>The Ext rule3 field must be a valid custom_validation2.</br>Field Ext rule3 must be valid.</br>The Ext rule5 field must be a valid custom_validation.</br>The Ext rule5 field must be a valid custom_validation2.</br>Field Ext rule5 must be valid.</br>');
	});
	
});

test('extension_rule_inversed_passed', function()
{
	testAjd(function($v)
    {
		$v->Notcustom_validation()->Notcustom_validation2('a')->Notnew_test('a')->check('ext_rule6', 'b');

		AJD_validation::Notcustom_validation()->Notcustom_validation2('a')->Notnew_test('a')->check('ext_rule7', 'b');

		testAjdErrorMatcher($v, '');
	});
	
});

test('extension_rule_inversed_fails', function()
{
	testAjd(function($v)
    {
		$v->Notcustom_validation()->Notcustom_validation2('a')->Notnew_test('a')->check('ext_rule8', 'a');

		AJD_validation::Notcustom_validation()->Notcustom_validation2('a')->Notnew_test('a')->check('ext_rule9', 'a');

		testAjdErrorMatcher($v, 'The Ext rule8 field must not be a valid custom_validation.</br>The Ext rule8 field must be a valid custom_validation2.</br>Field Ext rule8 must not be valid.</br>The Ext rule9 field must not be a valid custom_validation.</br>The Ext rule9 field must be a valid custom_validation2.</br>Field Ext rule9 must not be valid.</br>');
	});
	
});

test('extension_rule_w_anon_passed', function()
{
	testAjd(function($v)
    {
		$v
		->custom_validation()
		->custom_validation2('a')
		->new_test('a')
		->ext1_anontest('a')
		->ext2_anontest('a')
		->check('ext_anon_rule', 'a');

		AJD_validation::custom_validation()
		->custom_validation2('a')
		->new_test('a')
		->ext1_anontest('a')
		->ext2_anontest('a')
		->check('ext_anon_rule2', 'a');

		testAjdErrorMatcher($v, '');
	});
	
});

test('extension_rule_w_anon_fails', function()
{
	testAjd(function($v)
    {
		$v
		->custom_validation()
		->custom_validation2('a')
		->new_test('a')
		->ext1_anontest('a')
		->ext2_anontest('a')
		->check('ext_anon_rule3', '');

		AJD_validation::custom_validation()
		->custom_validation2('a')
		->new_test('a')
		->ext1_anontest('a')
		->ext2_anontest('a')
		->check('ext_anon_rule5', '');

		testAjdErrorMatcher($v, 'The Ext anon rule3 field must be a valid custom_validation.</br>The Ext anon rule3 field must be a valid custom_validation2.</br>Field Ext anon rule3 must be valid.</br>The Ext anon rule3 field must be a valid ext1_anontest.</br>The Ext anon rule3 field must be a valid ext2_anontest.</br>The Ext anon rule5 field must be a valid custom_validation.</br>The Ext anon rule5 field must be a valid custom_validation2.</br>Field Ext anon rule5 must be valid.</br>The Ext anon rule5 field must be a valid ext1_anontest.</br>The Ext anon rule5 field must be a valid ext2_anontest.</br>');
	});
});

test('extension_rule_w_anon_change_lang_fails', function()
{
	testAjd(function($v)
    {
    	$v->setLang(LANG::FIL);

		$v
		->custom_validation()
		->custom_validation2('a')
		->new_test('a')
		->ext1_anontest('a')
		->ext2_anontest('a')
		->check('ext_anon_rule6', '');

		AJD_validation::custom_validation()
		->custom_validation2('a')
		->new_test('a')
		->ext1_anontest('a')
		->ext2_anontest('a')
		->check('ext_anon_rule7', '');

		$v->setLang(LANG::EN);
		testAjdErrorMatcher($v, 'The Ext anon rule6 field must be a valid custom_validation.</br>The Ext anon rule6 field must be a valid custom_validation2.</br>Field Ext anon rule6 must be valid.</br>The Ext anon rule6 field must be a valid ext1_anontest.</br>The Ext anon rule6 field ay dapat na tama na ext2_anontest.</br>The Ext anon rule7 field must be a valid custom_validation.</br>The Ext anon rule7 field must be a valid custom_validation2.</br>Field Ext anon rule7 must be valid.</br>The Ext anon rule7 field must be a valid ext1_anontest.</br>The Ext anon rule7 field ay dapat na tama na ext2_anontest.</br>');
	});
});

test('extension_rule_w_anon_inversed_passed', function()
{
	testAjd(function($v)
    {
		$v
		->Notcustom_validation()
		->Notcustom_validation2('a')
		->Notnew_test('a')
		->Notext1_anontest('a')
		->Notext2_anontest('a')
		->check('ext_anon_rule8', 'b');

		AJD_validation::Notcustom_validation()
		->Notcustom_validation2('a')
		->Notnew_test('a')
		->Notext1_anontest('a')
		->Notext2_anontest('a')
		->check('ext_anon_rule9', 'b');

		testAjdErrorMatcher($v, '');
	});
	
});

test('extension_rule_w_anon_inversed_fails', function()
{
	testAjd(function($v)
    {
		$v
		->Notcustom_validation()
		->Notcustom_validation2('a')
		->Notnew_test('a')
		->Notext1_anontest('a')
		->Notext2_anontest('a')
		->check('ext_anon_rule10', 'a');

		AJD_validation::Notcustom_validation()
		->Notcustom_validation2('a')
		->Notnew_test('a')
		->Notext1_anontest('a')
		->Notext2_anontest('a')
		->check('ext_anon_rule11', 'a');

		testAjdErrorMatcher($v, 'The Ext anon rule10 field must not be a valid custom_validation.</br>The Ext anon rule10 field must be a valid custom_validation2.</br>Field Ext anon rule10 must not be valid.</br>The Ext anon rule10 field must not be a valid ext1_anontest.</br>The Ext anon rule10 field must not be a valid ext2_anontest.</br>The Ext anon rule11 field must not be a valid custom_validation.</br>The Ext anon rule11 field must be a valid custom_validation2.</br>Field Ext anon rule11 must not be valid.</br>The Ext anon rule11 field must not be a valid ext1_anontest.</br>The Ext anon rule11 field must not be a valid ext2_anontest.</br>');
	});
});

test('extension_rule_w_anon_change_lang_inversed_fails', function()
{
	testAjd(function($v)
    {
    	$v->setLang(LANG::FIL);

		$v
		->Notcustom_validation()
		->Notcustom_validation2('a')
		->Notnew_test('a')
		->Notext1_anontest('a')
		->Notext2_anontest('a')
		->check('ext_anon_rule12', 'a');

		AJD_validation::Notcustom_validation()
		->Notcustom_validation2('a')
		->Notnew_test('a')
		->Notext1_anontest('a')
		->Notext2_anontest('a')
		->check('ext_anon_rule14', 'a');

		$v->setLang(LANG::EN);

		testAjdErrorMatcher($v, 'The Ext anon rule12 field must not be a valid custom_validation.</br>The Ext anon rule12 field must be a valid custom_validation2.</br>Field Ext anon rule12 must not be valid.</br>The Ext anon rule12 field must not be a valid ext1_anontest.</br>The Ext anon rule12 field ay dapat hindi na tama na ext2_anontest.</br>The Ext anon rule14 field must not be a valid custom_validation.</br>The Ext anon rule14 field must be a valid custom_validation2.</br>Field Ext anon rule14 must not be valid.</br>The Ext anon rule14 field must not be a valid ext1_anontest.</br>The Ext anon rule14 field ay dapat hindi na tama na ext2_anontest.</br>');
	});
});

test('extension_macro_passed', function()
{
	testAjd(function($v)
    {
		$v->extension_macro()->extension_macro2()->check('macro1', '8');

		AJD_validation::extension_macro()->extension_macro2()->check('macro2', 8);

		testAjdErrorMatcher($v, '');
	});
	
});

test('extension_macro_fails', function()
{
	testAjd(function($v)
    {
    	$v->extension_macro()->extension_macro2()->check('macro3', '');

		AJD_validation::extension_macro()->extension_macro2()->check('macro5', 'a');

		testAjdErrorMatcher($v, 'The Macro3 field is required</br>Macro3 must be greater than or equal to 7. character(s). </br>Value Macro3 must be positive extentsion </br>Macro5 must be greater than or equal to 7. character(s). </br>Value Macro5 must be positive extentsion </br>');
	});
	
});

test('extension_multi_values_filter', function()
{
	testAjd(function($v)
    {
    	$toFiler = [
			'filter_test1' => ['a', 'b'],
			'filter_test2' => 'c'
		];
		

		$filteredValues = $v
			->Fcustom_string()
				->cacheFilter('filter_test1')
			->Fcustom_string()
				->cacheFilter('filter_test2')
			->filterAllValues($toFiler);

		foreach($filteredValues as $filtered)
		{
			$filtered = !is_array($filtered) ? [$filtered] : $filtered;

			expect($filtered)->each(function($value)
			{
				$value->toEndWith('_from_extension');
			});
		}
	});
	
});

test('extension_single_values_filter', function()
{
	testAjd(function($v)
    {
    	$filteredSingle = $v 
			->Fcustom_string()
			->Fwhite_space_option()

		->cacheFilter('fieldsingle')
		->filterValue('as   ');

		expect($filteredSingle)->toEndWith('_from_extension');
	});
	
});

test('extension_logic_passed', function()
{
	testAjd(function($v)
    {
    	$result = $v->Lgcustom_logics(1)->runLogics('1');

    	expect($result)->toBeTrue();
	});
	
});

test('extension_logic_fails', function()
{
	testAjd(function($v)
    {
    	$result = $v->Lgcustom_logics(2)->runLogics('1');

    	expect($result)->toBeFalse();
	});
	
});

test('extension_client_side', function()
{
	testAjd(function($v)
    {
    	$v->custom_validation(null, '#client_custom_validation')
    	->check('custom_validation');

    	$clientSide = $v->getClientSide();

    	$clientSide['custom_validation'] = preg_replace('/[\s]/', '', $clientSide['custom_validation']);

    	expect($clientSide)->toHaveKey('custom_validation', 'data-parsley-required="false"data-parsley-custom_validation="emailaass"data-parsley-custom_validation-message="TheCustomvalidationfieldmustbeavalidcustom_validation."');
    	
	});
	
});

test('sub_class_object_method_rule_passed', function()
{
	testAjd(function()
    {
    	$v = new SubClass;

		$v->sub_class(1)->check('sub_class_rule', 1);

		SubClass::sub_class(2)->check('sub_class_rule2', 2);

		testAjdErrorMatcher($v, '');
	});
	
});

test('sub_class_object_method_rule_fails', function()
{
	testAjd(function()
    {
    	$v = new SubClass;

		$v->sub_class(1)->check('sub_class_rule3', '');

		SubClass::sub_class(2)->check('sub_class_rule5', '');		

		testAjdErrorMatcher($v, 'Field Sub class rule3 of sub class rule must be valid.</br>Field Sub class rule5 of sub class rule must be valid.</br>');
	});
	
});


test('sub_class_object_method_rule_inversed_passed', function()
{
	testAjd(function()
    {
    	$v = new SubClass;

		$v->Notsub_class(1)->check('sub_class_rule6', '');

		SubClass::Notsub_class(2)->check('sub_class_rule27', '');

		testAjdErrorMatcher($v, '');
	});
	
});

test('sub_class_object_method_rule_inversed_fails', function()
{
	testAjd(function()
    {
    	$v = new SubClass;

		$v->Notsub_class(1)->check('sub_class_rule8', '1');

		SubClass::Notsub_class(2)->check('sub_class_rule9', '2');		

		testAjdErrorMatcher($v, 'Field Sub class rule8 of sub class rule must not be valid.</br>Field Sub class rule9 of sub class rule must not be valid.</br>');
	});
	
});