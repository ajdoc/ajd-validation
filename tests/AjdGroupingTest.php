<?php 

test('ajd_validation_groupings', function () 
{
	testAjd(function($v)
    {
		$v 
		->required()->groups(['t1'])
		->minlength(3)->groups(['t1'])

		
		->maxlength(5)->groups('t2')
		->alnum(['*', '&'])->groups('t2')

		->uncompromised()->groups('t3')

		->useGroupings(['t1'])
		
		->check('grouping_field', ['grouping_field' => ['', '']]);

		testAjdErrorMatcher($v, 'The Grouping field field is required at row 1.</br>The Grouping field field is required at row 2.</br>Grouping field must be greater than or equal to 3. character(s).  at row 1.</br>Grouping field must be greater than or equal to 3. character(s).  at row 2.</br>');
    });

    testAjd(function($v)
    {
		$v 
		->required()->groups(['t1'])
		->minlength(3)->groups(['t1'])

		
		->maxlength(5)->groups('t2')
		->alnum(['*', '&'])->groups('t2')

		->uncompromised()->groups('t3')

		// ->useGroupings($v->createGroupSequence(['t1', 't2', 't3']))

		->useGroupings(['t1', 't2'])
		
		->check('grouping_field', ['grouping_field' => ['', '']]);

		testAjdErrorMatcher($v, 'The Grouping field field is required at row 1.</br>The Grouping field field is required at row 2.</br>Grouping field must be greater than or equal to 3. character(s).  at row 1.</br>Grouping field must be greater than or equal to 3. character(s).  at row 2.</br>Grouping field must contain only letters (a-z), digits (0-9) and ""*&"". at row 1.</br>Grouping field must contain only letters (a-z), digits (0-9) and ""*&"". at row 2.</br>');
    });

    testAjd(function($v)
    {
		$v 
		->required()->groups(['t1'])
		->minlength(3)->groups(['t1'])

		
		->maxlength(5)->groups('t2')
		->alnum(['*', '&'])->groups('t2')

		->uncompromised()->groups('t3')

		->useGroupings(['t1', 't2', 't3'])
		
		->check('grouping_field', ['grouping_field' => ['', '']]);

		testAjdErrorMatcher($v, 'The Grouping field field is required at row 1.</br>The Grouping field field is required at row 2.</br>Grouping field must be greater than or equal to 3. character(s).  at row 1.</br>Grouping field must be greater than or equal to 3. character(s).  at row 2.</br>Grouping field must contain only letters (a-z), digits (0-9) and ""*&"". at row 1.</br>Grouping field must contain only letters (a-z), digits (0-9) and ""*&"". at row 2.</br>The Grouping field field has appeared in a data leak. at row 1.</br>The Grouping field field has appeared in a data leak. at row 2.</br>');
    });
});

test('ajd_validation_grouping_sequence', function () 
{
	testAjd(function($v)
    {
		$v 
		->required()->groups(['t1'])
		->minlength(3)->groups(['t1'])

		
		->maxlength(5)->groups('t2')
		->alnum(['*', '&'])->groups('t2')

		->uncompromised()->groups('t3')

		->useGroupings($v->createGroupSequence(['t1', 't2', 't3']))
		
		->check('grouping_field', ['grouping_field' => ['', '']]);

		testAjdErrorMatcher($v, 'The Grouping field field is required at row 1.</br>The Grouping field field is required at row 2.</br>Grouping field must be greater than or equal to 3. character(s).  at row 1.</br>Grouping field must be greater than or equal to 3. character(s).  at row 2.</br>');
    });

    testAjd(function($v)
    {
		$v 
		->required()->groups(['t1'])
		->minlength(3)->groups(['t1'])

		
		->maxlength(5)->groups('t2')
		->alnum(['*', '&'])->groups('t2')

		->uncompromised()->groups('t3')

		->useGroupings($v->createGroupSequence(['t1', 't2', 't3']))
		
		->check('grouping_field', ['grouping_field' => ['aasaa:', 'aaa:::']]);

		testAjdErrorMatcher($v, 'Grouping field must be less than or equal to 5. character(s).  at row 1.</br>Grouping field must be less than or equal to 5. character(s).  at row 2.</br>Grouping field must contain only letters (a-z), digits (0-9) and ""*&"". at row 1.</br>Grouping field must contain only letters (a-z), digits (0-9) and ""*&"". at row 2.</br>');
    });

    testAjd(function($v)
    {
		$v 
		->required()->groups(['t1'])
		->minlength(3)->groups(['t1'])

		
		->maxlength(5)->groups('t2')
		->alnum(['*', '&'])->groups('t2')

		->uncompromised()->groups('t3')

		->useGroupings($v->createGroupSequence(['t1', 't2', 't3']))
		
		->check('grouping_field', ['grouping_field' => ['aasa:', 'aaa:::']]);

		testAjdErrorMatcher($v, 'Grouping field must contain only letters (a-z), digits (0-9) and ""*&"". at row 1.</br>Grouping field must contain only letters (a-z), digits (0-9) and ""*&"". at row 2.</br>Grouping field must be less than or equal to 5. character(s).  at row 2.</br>');
    });

    testAjd(function($v)
    {
		$v 
		->required()->groups(['t1'])
		->minlength(3)->groups(['t1'])

		
		->maxlength(5)->groups('t2')
		->alnum(['*', '&'])->groups('t2')

		->uncompromised()->groups('t3')

		->useGroupings($v->createGroupSequence(['t1', 't2', 't3']))
		
		->check('grouping_field', ['grouping_field' => ['aas', 'aaa']]);

		testAjdErrorMatcher($v, 'The Grouping field field has appeared in a data leak. at row 1.</br>The Grouping field field has appeared in a data leak. at row 2.</br>');
    });

    testAjd(function($v)
    {
		$v 
		->required()->groups(['t1'])
		->minlength(3)->groups(['t1'])

		
		->maxlength(5)->groups('t2')
		->alnum(['*', '&'])->groups('t2')

		->uncompromised()->groups('t3')

		->useGroupings($v->createGroupSequence(['t1', 't2', 't3']))
		
		->check('grouping_field', ['grouping_field' => ['a*&er', 'aaa']]);

		testAjdErrorMatcher($v, 'The Grouping field field has appeared in a data leak. at row 2.</br>');
    });

    testAjd(function($v)
    {
		$v 
		->required()->groups(['t1'])
		->minlength(3)->groups(['t1'])

		
		->maxlength(5)->groups('t2')
		->alnum(['*', '&'])->groups('t2')

		->uncompromised()->groups('t3')

		->useGroupings($v->createGroupSequence(['t1', 't2', 't3']))
		
		->check('grouping_field', ['grouping_field' => ['a*&er', 'exaze']]);

		testAjdErrorMatcher($v, '');
    });
});