<?php

test('passed ajd validation required', function () 
{
    testAjd(function($v)
    {
        $this->assertTrue($v->required()->check('required', '1')->getValidationResult()->isValid());
    });
    
});

test('fails ajd validation required', function () 
{
    testAjd(function($v)
    {
        $this->assertFalse($v->required()->check('required', '')->getValidationResult()->isValid());
    });
});
