<?php

test('ajd_validation_stop_on_error_passed', function () 
{
    testAjd(function($v)
    {
        $v 
		->required()
		->minlength(2)->stopOnError()
		->email()
		->check('stop_on_error_passed1', 'aa@test.com');

		testAjdErrorMatcher($v, '');
    });
});

test('ajd_validation_stop_on_error_fails_on_first_should_stop_at_first', function () 
{
    testAjd(function($v)
    {
        $v 
		->required()->stopOnError()
		->minlength(2)
		->email()
		->check('stop_on_error_fails1', '');

		testAjdErrorMatcher($v, 'The Stop on error fails1 field is required</br>');
    });
});

test('ajd_validation_stop_on_error_fails_on_first_and_second_should_stop_at_second', function () 
{
    testAjd(function($v)
    {
        $v 
		->required()
		->minlength(2)->stopOnError()
		->email()
		->check('stop_on_error_fails2', '');

		testAjdErrorMatcher($v, 'The Stop on error fails2 field is required</br>Stop on error fails2 must be greater than or equal to 2. character(s). </br>');
    });
});

test('ajd_validation_stop_on_error_fails_on_first_second_and_third_should_stop_at_third_hence_must_print_all_error', function () 
{
    testAjd(function($v)
    {
        $v 
		->required()
		->minlength(2)
		->email()->stopOnError()
		->check('stop_on_error_fails3', '');

		testAjdErrorMatcher($v, 'The Stop on error fails3 field is required</br>Stop on error fails3 must be greater than or equal to 2. character(s). </br>The Stop on error fails3 field must be a valid email.</br>');
    });
});

test('ajd_validation_on_edit_scenario_passed', function () 
{
    testAjd(function($v)
    {
        $v 
		->required()->stopOnError()->on('edit')
		->minlength(2)
		->email()->on('edit')
		->check('on_edit_passed1', 'aa');

		testAjdErrorMatcher($v, '');
    });
});

test('ajd_validation_on_edit_scenario_required_rule_fails', function () 
{
    testAjd(function($v)
    {
    	$v->trigger('edit');

        $v 
		->required()->on('edit')->stopOnError()
		->minlength(2)->on('add')
		->email()->on('edit')
		->check('on_edit_fails1', '');

		testAjdErrorMatcher($v, 'The On edit fails1 field is required</br>');
    });
});

test('ajd_validation_on_edit_scenario_email_rule_fails', function () 
{
    testAjd(function($v)
    {
    	$v->trigger('edit');

        $v 
		->required()->on('edit')->stopOnError()
		->minlength(2)->on('add')
		->email()->on('edit')
		->check('on_edit_fails3', 'a');

		testAjdErrorMatcher($v, 'The On edit fails3 field must be a valid email.</br>');
    });
});

test('ajd_validation_sometimes_scenario_minlength_will_not_trigger_using_default_sometimes', function () 
{
    testAjd(function($v)
    {
        $v 
		->required()
		->minlength(2)->sometimes()
		->check('sometimes1', '');

		testAjdErrorMatcher($v, 'The Sometimes1 field is required</br>');
    });
});

test('ajd_validation_sometimes_scenario_minlength_will_trigger_using_default_sometimes', function () 
{
    testAjd(function($v)
    {
        $v 
		->required()
		->minlength(2)->sometimes()
		->check('sometimes2', 'a');

		testAjdErrorMatcher($v, 'Sometimes2 must be greater than or equal to 2. character(s). </br>');
    });
});

test('ajd_validation_sometimes_scenario_minlength_will_not_trigger_using_anonymous_function', function () 
{
    testAjd(function($v)
    {
        $v 
		->required()
		->minlength(3)->sometimes(function($value, $satisfier, $orig_field, $arrKey)
		{
			return !empty($value) && strlen($value) >= 1;
		})
		->check('sometimes3', '');

		testAjdErrorMatcher($v, 'The Sometimes3 field is required</br>');
    });
});

test('ajd_validation_sometimes_scenario_minlength_will_trigger_using_anonymous_function', function () 
{
    testAjd(function($v)
    {
        $v 
		->required()
		->minlength(3)->sometimes(function($value, $satisfier, $orig_field, $arrKey)
		{
			return !empty($value) && strlen($value) >= 1;
		})
		->check('sometimes4', 'a');

		testAjdErrorMatcher($v, 'Sometimes4 must be greater than or equal to 3. character(s). </br>');
    });
});

test('ajd_validation_sometimes_scenario_minlength_will_not_trigger_using_object_method', function () 
{
    testAjd(function($v)
    {
    	class Sometimes5
    	{
    		public function test($value, $satisfier, $orig_field, $arrKey)
    		{
    			return !empty($value) && strlen($value) >= 1;
    		}
    	}

        $v 
		->required()
		->minlength(3)->sometimes([new Sometimes5, 'test'])
		->check('sometimes5', '');

		testAjdErrorMatcher($v, 'The Sometimes5 field is required</br>');
    });
});

test('ajd_validation_sometimes_scenario_minlength_will_trigger_using_object_method', function () 
{
    testAjd(function($v)
    {
    	class Sometimes6
    	{
    		public function test($value, $satisfier, $orig_field, $arrKey)
    		{
    			return !empty($value) && strlen($value) >= 1;
    		}
    	}

        $v 
		->required()
		->minlength(3)->sometimes([new Sometimes6, 'test'])
		->check('sometimes6', 'a');

		testAjdErrorMatcher($v, 'Sometimes6 must be greater than or equal to 3. character(s). </br>');
    });
});

test('ajd_validation_sometimes_scenario_minlength_will_not_trigger_using_invokable_class', function () 
{
    testAjd(function($v)
    {
    	class Sometimes7
    	{
    		public function __invoke($value, $satisfier, $orig_field, $arrKey)
    		{
    			return !empty($value) && strlen($value) >= 1;
    		}
    	}

        $v 
		->required()
		->minlength(3)->sometimes(new Sometimes7)
		->check('sometimes7', '');

		testAjdErrorMatcher($v, 'The Sometimes7 field is required</br>');
    });
});

test('ajd_validation_sometimes_scenario_minlength_will_trigger_using_invokable_class', function () 
{
    testAjd(function($v)
    {
    	class Sometimes8
    	{
    		public function __invoke($value, $satisfier, $orig_field, $arrKey)
    		{
    			return !empty($value) && strlen($value) >= 1;
    		}
    	}

        $v 
		->required()
		->minlength(3)->sometimes(new Sometimes8)
		->check('sometimes8', 'a');

		testAjdErrorMatcher($v, 'Sometimes8 must be greater than or equal to 3. character(s). </br>');
    });
});

test('ajd_validation_sometimes_scenario_minlength_will_not_trigger_using_expression', function () 
{
    testAjd(function($v)
    {

        $v 
		->required()
		->minlength(3)->sometimes('value >= tax', ['tax' => 1])
		->check('sometimes9', '');

		testAjdErrorMatcher($v, 'The Sometimes9 field is required</br>');
    });
});

test('ajd_validation_sometimes_scenario_minlength_will_trigger_using_expression', function () 
{
    testAjd(function($v)
    {

        $v 
		->required()
		->minlength(3)->sometimes('value >= tax', ['tax' => 1])
		->check('sometimes10', '1');

		testAjdErrorMatcher($v, 'Sometimes10 must be greater than or equal to 3.</br>');
    });
});

test('ajd_validation_sometimes_scenario_minlength_will_not_trigger_using_boolean', function () 
{
    testAjd(function($v)
    {

        $v 
		->required()
		->minlength(3)->sometimes(false)
		->check('sometimes11', '');

		testAjdErrorMatcher($v, 'The Sometimes11 field is required</br>');
    });
});

test('ajd_validation_sometimes_scenario_minlength_will_trigger_using_boolean', function () 
{
    testAjd(function($v)
    {
        $v 
		->required()
		->minlength(3)->sometimes(true)
		->check('sometimes12', 'a');

		testAjdErrorMatcher($v, 'Sometimes12 must be greater than or equal to 3. character(s). </br>');
    });
});

test('ajd_validation_test_required_bail', function () 
{
	testAjd(function($v)
    {
        $v 
        ->bail()
		->required()
		->minlength(3)
		->email()
		->check('bail1', '');

		testAjdErrorMatcher($v, 'The Bail1 field is required</br>');
    });
});

test('ajd_validation_test_minlength_bail', function () 
{
	testAjd(function($v)
    {
        $v 
        ->bail()
		->required()
		->minlength(3)
		->email()
		->check('bail2', 'a');

		testAjdErrorMatcher($v, 'Bail2 must be greater than or equal to 3. character(s). </br>');
    });
});

test('ajd_validation_test_email_bail', function () 
{
	testAjd(function($v)
    {
        $v 
        ->bail()
		->required()
		->minlength(3)
		->email()
		->check('bail3', 'aaa');

		testAjdErrorMatcher($v, 'The Bail3 field must be a valid email.</br>');
    });
});