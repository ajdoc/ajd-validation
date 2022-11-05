<?php 

test('ajd when passed all and runtime', function()
{
    testAjd(function($v)
    {
            $v->when()
                ->Givrequired()
                ->Givminlength(2)
            ->endgiven('lgonlyfield22', 'aa', 'and')

                ->Givrequired()
            ->endgiven('lgonlyfield21', 'aa', 'and')

                ->Threquired()
            ->endthen('thenprintthis1')

                ->Othrequired()
            ->endotherwise('elseprintthis')
        ->endwhen();

        testAjdErrorMatcher($v, 'The Thenprintthis1 field is required</br>');
    });
});

test('ajd when fails and runtime', function()
{
    testAjd(function($v)
    {
            $v->when()
                ->Givrequired()
                ->Givminlength(2)
            ->endgiven('lgonlyfield22', 'aa', 'and')

                ->Givrequired()
            ->endgiven('lgonlyfield21', '', 'and')

                ->Threquired()
            ->endthen('thenprintthis1')

                ->Othrequired()
            ->endotherwise('elseprintthis')
        ->endwhen();

        testAjdErrorMatcher($v, 'The Elseprintthis field is required</br>');
    });
});

test('ajd when passed xor runtime', function()
{
    testAjd(function($v)
    {
            $v->when()
                ->Givrequired()
                ->Givminlength(2)
            ->endgiven('lgonlyfield22', 'aa', 'xor')

                ->Givrequired()
            ->endgiven('lgonlyfield21', '', 'xor')

                ->Threquired()
            ->endthen('thenprintthis1')

                ->Othrequired()
            ->endotherwise('elseprintthis')
        ->endwhen();

        testAjdErrorMatcher($v, 'The Thenprintthis1 field is required</br>');
    });
});

test('ajd when fails xor runtime', function()
{
    testAjd(function($v)
    {
        $v
        ->when()
            ->Givrequired()
            ->Givminlength(2)
        ->endgiven('lgonlyfield22', 'aa', 'xor')

            ->Givrequired()
        ->endgiven('lgonlyfield21', 'aa', 'xor')

            ->Threquired()
        ->endthen('thenprintthis1')

            ->Othrequired()
        ->endotherwise('elseprintthis')
        ->endwhen();

        testAjdErrorMatcher($v, 'The Elseprintthis field is required</br>');
    });
});

test('ajd when passed or runtime', function()
{
    testAjd(function($v)
    {
        $v->when()
                ->Givrequired()
                ->Givminlength(2)
            ->endgiven('lgonlyfield22', 'aa', 'or')

                ->Givrequired()
            ->endgiven('lgonlyfield21', '', 'or')

                ->Threquired()
            ->endthen('thenprintthis1')

                ->Othrequired()
            ->endotherwise('elseprintthis')
        ->endwhen();

        testAjdErrorMatcher($v, 'The Thenprintthis1 field is required</br>');
    });
});

test('ajd when fails or runtime', function()
{
    testAjd(function($v)
    {
        $v->when()
                ->Givrequired()
                ->Givminlength(2)
            ->endgiven('lgonlyfield22', '', 'or')

                ->Givrequired()
            ->endgiven('lgonlyfield21', '', 'or')

                ->Threquired()
            ->endthen('thenprintthis1')

                ->Othrequired()
            ->endotherwise('elseprintthis')
        ->endwhen();

        testAjdErrorMatcher($v, 'The Elseprintthis field is required</br>');
    });
});

test('ajd when passed combination runtime', function()
{
    testAjd(function($v)
    {
        $v->when()
                ->Lgfirst(true)
                ->Givrequired()
            ->endgiven('lgonlyfield1', '', 'and')

                ->Givrequired()
                ->Givminlength(2)
            ->endgiven('lgonlyfield22', '', 'xor')

                ->Givrequired()
            ->endgiven('lgonlyfield21', 'a', 'or')

                ->Threquired()
            ->endthen('thenprintthis1')

                ->Othrequired()
            ->endotherwise('elseprintthis')
        ->endwhen();

        testAjdErrorMatcher($v, 'The Thenprintthis1 field is required</br>');
    });
});

test('ajd when fails combination runtime', function()
{
    testAjd(function($v)
    {
        $v->when()
                ->Lgfirst(true)
                ->Givrequired()
            ->endgiven('lgonlyfield1', 'a', 'and')

                ->Givrequired()
                ->Givminlength(2)
            ->endgiven('lgonlyfield22', '', 'xor')

                ->Givrequired()
            ->endgiven('lgonlyfield21', 'a', 'or')

                ->Threquired()
            ->endthen('thenprintthis1')

                ->Othrequired()
            ->endotherwise('elseprintthis')
        ->endwhen();

        testAjdErrorMatcher($v, 'The Elseprintthis field is required</br>');
    });
});

test('ajd when registering a new logic using anonymous class passed', function()
{
    testAjd(function($v)
    {
        $v->registerLogic('new_logic1', new class() extends \AJD_validation\Contracts\AbstractAnonymousLogics
        {
            public function __invoke($value, $parameters = null)
            {
                return $this->getExtraArgs()[0];
            }
        }, [true]);

        $v->when()
                ->Lgfirst(true)
                ->Lgnew_logic1()
                ->Givrequired()
            ->endgiven('lgonlyfield1', 'a', 'and')

                ->Givrequired()
                ->Givminlength(2)
            ->endgiven('lgonlyfield22', '', 'xor')

                ->Givrequired()
            ->endgiven('lgonlyfield21', '', 'or')

                ->Threquired()
            ->endthen('thenprintthis1')

                ->Othrequired()
            ->endotherwise('elseprintthis')
        ->endwhen();

        testAjdErrorMatcher($v, 'The Thenprintthis1 field is required</br>');
    });
});

test('ajd when registering a new logic using anonymous class fails', function()
{
    testAjd(function($v)
    {
        $v->registerLogic('new_logic2', new class() extends \AJD_validation\Contracts\AbstractAnonymousLogics
        {
            public function __invoke($value, $parameters = null)
            {
                return $this->getExtraArgs()[0];
            }
        }, [false]);

        $v->when()
                ->Lgfirst(true)
                ->Lgnew_logic2()
                ->Givrequired()
            ->endgiven('lgonlyfield1', 'a', 'and')

                ->Givrequired()
                ->Givminlength(2)
            ->endgiven('lgonlyfield22', '', 'xor')

                ->Givrequired()
            ->endgiven('lgonlyfield21', '', 'or')

                ->Threquired()
            ->endthen('thenprintthis1')

                ->Othrequired()
            ->endotherwise('elseprintthis')
        ->endwhen();

        testAjdErrorMatcher($v, 'The Elseprintthis field is required</br>');
    });
});

test('ajd when registering a new logic using anonymous function passed', function()
{
    testAjd(function($v)
    {
        $v->registerLogic('anonfunc1', function($value, $obj)
        {
            if(!$obj->forGetValues)
            {
                return $obj->getExtraArgs()[0];   
            }

        }, [true]);

        $v->when()
                ->Lgfirst(true)
                ->Lganonfunc1()
                ->Givrequired()
            ->endgiven('lgonlyfield1', 'a', 'and')

                ->Givrequired()
                ->Givminlength(2)
            ->endgiven('lgonlyfield22', '', 'xor')

                ->Givrequired()
            ->endgiven('lgonlyfield21', '', 'or')

                ->Threquired()
            ->endthen('thenprintthis1')

                ->Othrequired()
            ->endotherwise('elseprintthis')
        ->endwhen();

        testAjdErrorMatcher($v, 'The Thenprintthis1 field is required</br>');
    });
});

test('ajd when registering a new logic using anonymous function fails', function()
{
    testAjd(function($v)
    {
        $v->registerLogic('anonfunc2', function($value, $obj)
        {
            if(!$obj->forGetValues)
            {
                return $obj->getExtraArgs()[0];   
            }

        }, [false]);

        $v->when()
                ->Lgfirst(true)
                ->Lganonfunc2()
                ->Givrequired()
            ->endgiven('lgonlyfield1', 'a', 'and')

                ->Givrequired()
                ->Givminlength(2)
            ->endgiven('lgonlyfield22', '', 'xor')

                ->Givrequired()
            ->endgiven('lgonlyfield21', '', 'or')

                ->Threquired()
            ->endthen('thenprintthis1')

                ->Othrequired()
            ->endotherwise('elseprintthis')
        ->endwhen();

        testAjdErrorMatcher($v, 'The Elseprintthis field is required</br>');
    });
});

test('ajd when registering a new logic using object method passed', function()
{
    testAjd(function($v)
    {
        class TestMethod1
        {
            public function test($value, $obj)
            {
                if(!$obj->forGetValues)
                {
                    return $obj->getExtraArgs()[0];   
                }
            }
        }

        $v->registerLogic('objmethod1', [new TestMethod1, 'test'], [true]);

        $v->when()
                ->Lgfirst(true)
                ->Lgobjmethod1()
                ->Givrequired()
            ->endgiven('lgonlyfield1', 'a', 'and')

                ->Givrequired()
                ->Givminlength(2)
            ->endgiven('lgonlyfield22', '', 'xor')

                ->Givrequired()
            ->endgiven('lgonlyfield21', '', 'or')

                ->Threquired()
            ->endthen('thenprintthis1')

                ->Othrequired()
            ->endotherwise('elseprintthis')
        ->endwhen();

        testAjdErrorMatcher($v, 'The Thenprintthis1 field is required</br>');
    });
});

test('ajd when registering a new logic using object method fails', function()
{
    testAjd(function($v)
    {
        class TestMethod2
        {
            public function test($value, $obj)
            {
                if(!$obj->forGetValues)
                {
                    return $obj->getExtraArgs()[0];   
                }
            }
        }

        $v->registerLogic('objmethod2', [new TestMethod2, 'test'], [false]);

        $v->when()
                ->Lgfirst(true)
                ->Lgobjmethod2()
                ->Givrequired()
            ->endgiven('lgonlyfield1', 'a', 'and')

                ->Givrequired()
                ->Givminlength(2)
            ->endgiven('lgonlyfield22', '', 'xor')

                ->Givrequired()
            ->endgiven('lgonlyfield21', '', 'or')

                ->Threquired()
            ->endthen('thenprintthis1')

                ->Othrequired()
            ->endotherwise('elseprintthis')
        ->endwhen();

        testAjdErrorMatcher($v, 'The Elseprintthis field is required</br>');
    });
});

test('ajd when registering a new logic using invokable class passed', function()
{
    testAjd(function($v)
    {
        class InvokeTest1
        {
            public function __invoke($value, $obj)
            {
                if(!$obj->forGetValues)
                {
                    return $obj->getExtraArgs()[0];   
                }
            }
        }

        $v->registerLogic('invokeclass1', new InvokeTest1, [true]);

        $v->when()
                ->Lgfirst(true)
                ->Lginvokeclass1()
                ->Givrequired()
            ->endgiven('lgonlyfield1', 'a', 'and')

                ->Givrequired()
                ->Givminlength(2)
            ->endgiven('lgonlyfield22', '', 'xor')

                ->Givrequired()
            ->endgiven('lgonlyfield21', '', 'or')

                ->Threquired()
            ->endthen('thenprintthis1')

                ->Othrequired()
            ->endotherwise('elseprintthis')
        ->endwhen();

        testAjdErrorMatcher($v, 'The Thenprintthis1 field is required</br>');
    });
});

test('ajd when registering a new logic using invokable class fails', function()
{
    testAjd(function($v)
    {
        class InvokeTest2
        {
            public function __invoke($value, $obj)
            {
                if(!$obj->forGetValues)
                {
                    return $obj->getExtraArgs()[0];   
                }
            }
        }

        $v->registerLogic('invokeclass2', new InvokeTest2, [false]);

        $v->when()
                ->Lgfirst(true)
                ->Lginvokeclass2()
                ->Givrequired()
            ->endgiven('lgonlyfield1', 'a', 'and')

                ->Givrequired()
                ->Givminlength(2)
            ->endgiven('lgonlyfield22', '', 'xor')

                ->Givrequired()
            ->endgiven('lgonlyfield21', '', 'or')

                ->Threquired()
            ->endthen('thenprintthis1')

                ->Othrequired()
            ->endotherwise('elseprintthis')
        ->endwhen();

        testAjdErrorMatcher($v, 'The Elseprintthis field is required</br>');
    });

});

test('ajd when alternative syntax triggers then', function()
{
    testAjd(function($v)
    {
        $v->when()

            ->ifAnd()
                ->required()
                ->minlength(2)
            ->endIf('testwhennew1', 'aa')

            ->then()
                ->required()
                ->email()
            ->endthen('printthen1')

            ->then()
                ->required()
            ->endthen('printthen2')

            ->otherwise()
                ->required()
                ->digit()
            ->endotherwise('printtotherwise1')

        ->endwhen();

        testAjdErrorMatcher($v, 'The Printthen1 field is required</br>The Printthen1 field must be a valid email.</br>The Printthen2 field is required</br>');
    });
});

test('ajd when alternative syntax triggers otherwise', function()
{
    testAjd(function($v)
    {
        $v->when()

            ->ifAnd()
                ->required()
                ->minlength(2)
            ->endIf('testwhennew1', '')

            ->then()
                ->required()
                ->email()
            ->endthen('printthen1')
            ->then()
                ->required()
            ->endthen('printthen2')

            ->otherwise()
                ->required()
                ->digit()
            ->endotherwise('printtotherwise1')

        ->endwhen();

        testAjdErrorMatcher($v, 'The Printtotherwise1 field is required</br>Printtotherwise1 must contain only digits (0-9).</br>');
    });
});

test('ajd when alternative syntax complex logic triggers then', function()
{
    testAjd(function($v)
    {
        $v->when()

            ->ifAnd()
                ->Lgfirst(true)
                ->required()
            ->endIf('lgonlyfield1', 'a')

            ->ifXor()
                ->required()
                ->minlength(2)
            ->endIf('lgonlyfield22', '')

            ->ifOr()
                ->required()
            ->endIf('lgonlyfield21', '')

            ->then()
                ->required()
            ->endthen('thenprintthis1')

            ->otherwise()
                ->required()
            ->endotherwise('elseprintthis')

        ->endwhen();

        testAjdErrorMatcher($v, 'The Thenprintthis1 field is required</br>');
    });

});

test('ajd when alternative syntax complex logic triggers otherwise', function()
{
    testAjd(function($v)
    {
        $v->when()
        
            ->ifAnd()
                ->Lgfirst(true)
                ->required()
            ->endIf('lgonlyfield1', 'a')

            ->ifXor()
                ->required()
                ->minlength(2)
            ->endIf('lgonlyfield22', 'aa')

            ->ifOr()
                ->required()
            ->endIf('lgonlyfield21', '')

            ->then()
                ->required()
            ->endthen('thenprintthis1')

            ->otherwise()
                ->required()
            ->endotherwise('elseprintthis')

        ->endwhen();

        testAjdErrorMatcher($v, 'The Elseprintthis field is required</br>');
    });

});

test('ajd when alternative syntax if,elseif,otherwise with or rule digit trigger then', function()
{
    testAjd(function($v)
    {
        $v->when()
        
            ->ifAnd()
                ->Lgfirst(true)
                ->oRemail()
                ->oRdigit()
            ->endIf('lgonlyfield1', '1')

                ->then()
                    ->required()
                ->endthen('thenprintthis1')

            ->elseIfAnd(true)
                ->required()
            ->endElseIf('elseiffield1', '')

            ->elseIfOr()
                ->required()
            ->endElseIf('elseiffield2', '')

                ->then()
                    ->required()
                ->endthen('thenprintthis2')

            ->elseIfOr(true)
                ->required()
            ->endElseIf('elseiffield3', '')

                ->then()
                    ->required()
                ->endthen('thenprintthis3')

            ->otherwise()
                ->required()
            ->endotherwise('elseprintthis')

        ->endwhen();

        testAjdErrorMatcher($v, 'The Thenprintthis1 field is required</br>');
    });
});

test('ajd when alternative syntax if,elseif,otherwise with or rule email trigger then', function()
{
    testAjd(function($v)
    {
        $v->when()
        
            ->ifAnd()
                ->Lgfirst(true)
                ->oRemail()
                ->oRdigit()
            ->endIf('lgonlyfield1', 'a@test.com')

                ->then()
                    ->required()
                ->endthen('thenprintthis1')

            ->elseIfAnd(true)
                ->required()
            ->endElseIf('elseiffield1', '')

            ->elseIfOr()
                ->required()
            ->endElseIf('elseiffield2', '')

                ->then()
                    ->required()
                ->endthen('thenprintthis2')

            ->elseIfOr(true)
                ->required()
            ->endElseIf('elseiffield3', '')

                ->then()
                    ->required()
                ->endthen('thenprintthis3')

            ->otherwise()
                ->required()
            ->endotherwise('elseprintthis')

        ->endwhen();

        testAjdErrorMatcher($v, 'The Thenprintthis1 field is required</br>');
    });
});

test('ajd when alternative syntax if,elseif,otherwise with or rule trigger elsethen first', function()
{
    testAjd(function($v)
    {
        $v->when()
        
            ->ifAnd()
                ->Lgfirst(false)
                ->oRemail()
                ->oRdigit()
            ->endIf('lgonlyfield1', '')

                ->then()
                    ->required()
                ->endthen('thenprintthis1')

            ->elseIfAnd(true)
                ->required()
            ->endElseIf('elseiffield1', '')

            ->elseIfOr()
                ->required()
            ->endElseIf('elseiffield2', 'a')

                ->then()
                    ->required()
                ->endthen('thenprintthis2')

            ->elseIfOr(true)
                ->required()
            ->endElseIf('elseiffield3', '')

                ->then()
                    ->required()
                ->endthen('thenprintthis3')

            ->otherwise()
                ->required()
            ->endotherwise('elseprintthis')

        ->endwhen();

        testAjdErrorMatcher($v, 'The Thenprintthis2 field is required</br>');
    });
});

test('ajd when alternative syntax if,elseif,otherwise with or rule trigger elsethen second', function()
{
    testAjd(function($v)
    {
        $v->when()
        
            ->ifAnd()
                ->Lgfirst(false)
                ->oRemail()
                ->oRdigit()
            ->endIf('lgonlyfield1', '')

                ->then()
                    ->required()
                ->endthen('thenprintthis1')

            ->elseIfAnd(true)
                ->required()
            ->endElseIf('elseiffield1', '')

            ->elseIfOr()
                ->required()
            ->endElseIf('elseiffield2', '')

                ->then()
                    ->required()
                ->endthen('thenprintthis2')

            ->elseIfOr(true)
                ->required()
            ->endElseIf('elseiffield3', 'a')

                ->then()
                    ->required()
                ->endthen('thenprintthis3')

            ->otherwise()
                ->required()
            ->endotherwise('elseprintthis')

        ->endwhen();

        testAjdErrorMatcher($v, 'The Thenprintthis3 field is required</br>');
    });
});

test('ajd when alternative syntax if,elseif,otherwise with or rule trigger otherwise', function()
{
    testAjd(function($v)
    {
        $v->when()
        
            ->ifAnd()
                ->Lgfirst(false)
                ->oRemail()
                ->oRdigit()
            ->endIf('lgonlyfield1', '')

                ->then()
                    ->required()
                ->endthen('thenprintthis1')

            ->elseIfAnd(true)
                ->required()
            ->endElseIf('elseiffield1', '')

            ->elseIfOr()
                ->required()
            ->endElseIf('elseiffield2', '')

                ->then()
                    ->required()
                ->endthen('thenprintthis2')

            ->elseIfOr(true)
                ->required()
            ->endElseIf('elseiffield3', '')

                ->then()
                    ->required()
                ->endthen('thenprintthis3')

            ->otherwise()
                ->required()
            ->endotherwise('elseprintthis')

        ->endwhen();

        testAjdErrorMatcher($v, 'The Elseprintthis field is required</br>');
    });
});