<?php 

use AJD_validation\AJD_validation;

$v = new AJD_validation;
$v->registerRuleAlias('testalias', $v->getValidator()->required()->minlength(2), ['default' => 'new_mess', 'inverse' => 'mess iverse']);

$v->registerRuleAlias('testalias2', 
		$v->getValidator()
			->required()
				->setError(['default' => 'test new {field} required mess', 'inverse' => 'not aaa'])
				->setClientSide()
			->minlength(2)
				->setError(['default' => 'test new minlength mess'])
				->setClientSide('client minlength alias2')

	);

	$v->registerRuleAlias('new_expression', 
		$v->getValidator()
			->expression('value + {tax}; 
				_expression_1 + 7;
				_expression_2 >= 10'
			)
				->setError(['default' => 'value must be equals to tax or equals', 'inverse' => 'value must not be equals to tax'])

	);

test('passed register rule alias', function()
{
	testAjd(function($v)
    {
        $v->testalias()->check('testalias', ['testalias' => 'aa']);

		testAjdErrorMatcher($v, '');
    });
});

test('fails register rule alias', function()
{
	testAjd(function($v)
    {
        $v->testalias()->check('testalias2', ['testalias2' => '']);

		testAjdErrorMatcher($v, 'new_mess</br>');
    });
});

test('passed register rule with new message per rule alias', function()
{
	testAjd(function($v)
    {
        $v->testalias2()->check('testalias3', ['testalias3' => 'aa']);

		testAjdErrorMatcher($v, '');
    });
});

test('fails register rule with new message per rule alias', function()
{
	testAjd(function($v)
    {
        $v->testalias2()->check('testalias4', ['testalias4' => '']);

		testAjdErrorMatcher($v, 'test new "Testalias4" required mess<br/>&nbsp;&nbsp;- test new minlength mess</br>');
    });
});

test('passed register inversed rule with new message per rule alias', function()
{
	testAjd(function($v)
    {
        $v->Nottestalias2()->check('testalias5', ['testalias5' => '']);

		testAjdErrorMatcher($v, '');
    });
});

test('fails register inversed rule with new message per rule alias', function()
{
	testAjd(function($v)
    {
        $v->Nottestalias2()->check('testalias6', ['testalias6' => 'aa']);

		testAjdErrorMatcher($v, 'not aaa<br/>&nbsp;&nbsp;- test new minlength mess</br>');
    });
});

test('passed register rule with new message per rule and with expression rule alias', function()
{
	testAjd(function($v)
    {
       	$v
        ->testalias2()
		->new_expression(['tax' => 1])
		->check('testalias7', ['testalias7' => '2']);

		testAjdErrorMatcher($v, '');
    });
});

test('fail register rule with new message per rule and with expression rule alias', function()
{
	testAjd(function($v)
    {
       	$v
		->testalias2()
		->new_expression(['tax' => 1])
		->check('testalias8', ['testalias8' => 'a']);

		testAjdErrorMatcher($v, 'test new minlength mess</br>value must be equals to tax or equals</br>');
    });
});

test('passed register rule alias in the middle with new message per rule and with expression rule alias', function()
{
	testAjd(function($v)
    {
       	$v->registerRuleAlias('testalias3', 
			$v->getValidator()
				->required()
					->setError(['default' => 'test new {field} required mess 3', 'inverse' => 'not aaa 3'])
					->setClientSide()
				->minlength(3)
					->setError(['default' => 'test new minlength mess 3'])
					->setClientSide()

		);

		$v
		->testalias()
		->testalias3()
		->testalias2()
		->new_expression(['tax' => 1])
		->check('testalias9', ['testalias9' => '3']);

		testAjdErrorMatcher($v, '');
    });
});

test('fails register rule alias in the middle with new message per rule and with expression rule alias', function()
{
	testAjd(function($v)
    {
       	$v
		->testalias()
		->testalias3()
		->testalias2()
		->new_expression(['tax' => 1])
		->check('testalias10', ['testalias10' => '']);

		testAjdErrorMatcher($v, 'new_mess</br>test new "Testalias10" required mess 3<br/>&nbsp;&nbsp;- test new minlength mess 3</br>test new "Testalias10" required mess<br/>&nbsp;&nbsp;- test new minlength mess</br>value must be equals to tax or equals</br>');
    });
});