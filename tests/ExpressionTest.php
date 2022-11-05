<?php

use AJD_validation\Helpers\Expression;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertNull;
use function PHPUnit\Framework\assertTrue;

test('empty expression', function () {
    $expr = new Expression();
    assertNull($expr->evaluate(''));
    assertNull($expr->evaluate('null'));
    assertNull($expr->evaluate('NULL'));
    assertNull($expr->evaluate('NulL'));
});

test('string parsing', function () {
    $expr = new Expression();

    assertEquals(
        $expr->evaluate('" \' \" ` ) ( ] [ } { , "'),
        " ' \" ` ) ( ] [ } { , "
    );

    assertEquals(
        $expr->evaluate("' \' \" ` ) ( ] [ } { , '"),
        " ' \" ` ) ( ] [ } { , "
    );

    assertEquals(
        $expr->evaluate("` ' \" \` ) ( ] [ } { , `"),
        " ' \" ` ) ( ] [ } { , "
    );
});

test('string concat', function () {
    $expr = new Expression([
        'text' => 'this is some string',
    ]);

    assertEquals($expr->evaluate('"foo" ~ \'bar\' ~ `baz`'), 'foobarbaz');
    assertEquals($expr->evaluate('"foo"~\'bar\'~`baz`'), 'foobarbaz');

    assertEquals($expr->evaluate('"foo" ~ 1 + 2 ~ `baz`'), 'foo3baz');
    assertEquals($expr->evaluate('"foo"~1+2~`baz`'), 'foo3baz');

    assertEquals($expr->evaluate('"message: " ~ text'), 'message: this is some string');
    assertEquals($expr->evaluate('"message: "~text'), 'message: this is some string');
    assertEquals($expr->evaluate("'message: ' ~ text"), 'message: this is some string');
    assertEquals($expr->evaluate("'message: '~text"), 'message: this is some string');
    assertEquals($expr->evaluate('`message: ` ~ text'), 'message: this is some string');
    assertEquals($expr->evaluate('`message: `~text'), 'message: this is some string');
});

test('number parsing', function () {
    $expr = new Expression();

    assertEquals($expr->evaluate('1'), 1);
    assertEquals($expr->evaluate('-1'), -1);
    assertEquals($expr->evaluate('1.0'), 1.0);
    assertEquals($expr->evaluate('-1.0'), -1.0);
    assertEquals($expr->evaluate('12.34'), 12.34);
    assertEquals($expr->evaluate('-12.34'), -12.34);
});

test('basic arithmetics', function () {
    $expr = new Expression();

    assertEquals($expr->evaluate('1 + 2'), 3);
    assertEquals($expr->evaluate('1 - 2'), -1);
    assertEquals($expr->evaluate('2 * 3'), 6);
    assertEquals($expr->evaluate('6 / 3'), 2);
    assertEquals($expr->evaluate('13 % 5'), 3);
    assertEquals($expr->evaluate('2 ** 3'), 8);

    assertEquals($expr->evaluate('2 * 2 + 2'), 6);
    assertEquals($expr->evaluate('2 * (2 + 2)'), 8);

    assertEquals($expr->evaluate('4 / 2 - 1'), 1);
    assertEquals($expr->evaluate('4 / (2 - 1)'), 4);

    $expr = new Expression([
        'number' => 123,
    ]);

    assertEquals($expr->evaluate('(number - 10 * 4 / 2 - 3) % 10'), 0);
    assertEquals($expr->evaluate('(number-10*4/2-3)%10'), 0);

    $expr = new Expression([
        'number' => '000123.0',
    ]);

    assertEquals($expr->evaluate('(number - 10.0 * 04.0 / 2.0 - 3.0) % 10.0'), 0);
    assertEquals($expr->evaluate('(number-10.0*04.0/2.0-3.0)%10.0'), 0);
});

test('arithmetics comparisons', function () {
    $expr = new Expression([
        'number' => 123,
        'negative' => false,
    ]);

    assertTrue($expr->evaluate('(100 + number * 1 <= 200 || number < -1) === negative'));
    assertTrue($expr->evaluate('(100+number*1<=200||number<-1)===negative'));

    assertFalse($expr->evaluate('0 > 1'));
    assertTrue($expr->evaluate('0 < 1'));

    assertFalse($expr->evaluate('1 < 1'));
    assertFalse($expr->evaluate('1 > 1'));

    assertTrue($expr->evaluate('1 <= 1'));
    assertTrue($expr->evaluate('1 >= 1'));

    assertTrue($expr->evaluate('0 <= 1'));
    assertTrue($expr->evaluate('1 >= 0'));

    assertFalse($expr->evaluate('1 <= 0'));
    assertFalse($expr->evaluate('0 >= 1'));
});

test('boolean expressions', function () {
    $expr = new Expression();

    assertTrue($expr->evaluate('true'));
    assertTrue($expr->evaluate('!false'));
    assertFalse($expr->evaluate('!true'));
    assertFalse($expr->evaluate('false'));

    assertTrue($expr->evaluate('true && false && false || true && true'));
    assertTrue($expr->evaluate('!false && !true && false || !false && true'));
    assertTrue($expr->evaluate('true&&false&&false||true&&true'));
    assertTrue($expr->evaluate('((true)&&false&&false||true&&true)'));
    assertFalse($expr->evaluate('!((true)&&false&&false||true&&true)'));

    assertFalse($expr->evaluate('true && false && (false || true) && true'));
    assertFalse($expr->evaluate('!false && !true && (false || !false) && true'));
    assertFalse($expr->evaluate('true&&false&&(false||true)&&true'));
    assertFalse($expr->evaluate('((true)&&false&&(false||true)&&true)'));
    assertTrue($expr->evaluate('!((true)&&false&&(false||true)&&true)'));
    
    assertTrue($expr->evaluate('true && false xor false || true'));
    assertFalse($expr->evaluate('!true && false xor false || !true'));

    assertTrue($expr->evaluate('false xor false || true'));
    assertFalse($expr->evaluate('false xor false xor false'));
});

test('array and object definitions', function () {
    $expr = new Expression([
        'key' => 'third'
    ]);

    assertEquals($expr->evaluate('[]'), []);
    assertEquals($expr->evaluate('[ "one", "two", 23, ]'), ["one", "two", 23]);
    assertEquals($expr->evaluate('["first": "one", "second": "two", key: 23]'), ["first" => "one", "second" => "two", "third" => 23]);
    assertEquals($expr->evaluate('["array": ["foo", `bar`]]'), ["array" => ["foo", "bar"]]);
    assertEquals($expr->evaluate('["array": {\'foo\', "bar",},]'), ["array" => (object) ["foo", "bar"]]);

    assertEquals($expr->evaluate('{}'), (object) []);
    assertEquals($expr->evaluate('{ "one", "two", 23, }'), (object) ["one", "two", 23]);
    assertEquals($expr->evaluate('{first: "one", second: "two", key: 23}'), (object) ["first" => "one", "second" => "two", "key" => 23]);
    assertEquals($expr->evaluate('{array: ["foo", `bar`]}'), (object) ["array" => ["foo", "bar"]]);
    assertEquals($expr->evaluate('{array: {\'foo\', "bar",},}'), (object) ["array" => (object) ["foo", "bar"]]);
});

test('access array elements', function () {
    $expr = new Expression([
        'hash' => ['first' => 'same', 'second' => 'different', 'third' => 'same', 'another' => ['deep' => 'text']],
        'key' => 'first'
    ]);

    assertEquals($expr->evaluate('hash.second'), 'different');
    assertEquals($expr->evaluate('hash["second"]'), 'different');
    assertEquals($expr->evaluate('hash[key]'), 'same');
    assertEquals($expr->evaluate('hash.another'), ['deep' => 'text']);

    assertEquals($expr->evaluate('hash.another.deep'), 'text');
    assertEquals($expr->evaluate('hash["another"]["deep"]'), 'text');
    assertEquals($expr->evaluate("hash['another']['deep']"), 'text');
    assertEquals($expr->evaluate('hash[`another`][`deep`]'), 'text');
});

test('equal comparisons', function () {
    $expr = new Expression();

    assertFalse($expr->evaluate('true == false'));
    assertFalse($expr->evaluate('true==false'));
    assertFalse($expr->evaluate('true === false'));
    assertFalse($expr->evaluate('true===false'));

    assertTrue($expr->evaluate('true == true'));
    assertTrue($expr->evaluate('true === true'));

    assertTrue($expr->evaluate('false == false'));
    assertTrue($expr->evaluate('false === false'));

    assertTrue($expr->evaluate('"" == false'));
    assertFalse($expr->evaluate('"" === false'));
    assertTrue($expr->evaluate('0 == false'));
    assertFalse($expr->evaluate('0 === false'));

    assertTrue($expr->evaluate('"sth" == true'));
    assertFalse($expr->evaluate('"sth" === true'));
    assertTrue($expr->evaluate('1 == true'));
    assertFalse($expr->evaluate('1 === true'));
});

test('closure calls', function () {
    $expr = new Expression([
        'param' => ['hello'],
        'params' => ['hello', 'world'],
        'named_params' => ['a' => 'hello', 'b' => 'world'],
        'simple' => fn ($a, $b) => $a . '-' . $b,
        'nested' => ['closure' => fn ($a) => fn (string $b): string => "you said: $a then $b"],
        'returns_array' => fn ($value) => ['key' => $value],
    ]);

    assertEquals($expr->evaluate('simple("hello", `world`)'), 'hello-world');
    assertEquals($expr->evaluate('simple(a: "hello", b: `world`)'), 'hello-world');
    assertEquals($expr->evaluate('simple(...params)'), 'hello-world');
    assertEquals($expr->evaluate('simple(...named_params)'), 'hello-world');
    assertEquals($expr->evaluate('simple(...param, \'world\')'), 'hello-world');

    assertEquals($expr->evaluate('nested.closure("hello")(`world`)'), 'you said: hello then world');
    assertEquals($expr->evaluate('nested[`closure`]("hello")(`world`)'), 'you said: hello then world');
    assertEquals($expr->evaluate('returns_array("foo").key'), 'foo');
    assertEquals($expr->evaluate('returns_array("foo")["key"]'), 'foo');
});

test('object property', function () {
    $expr = new Expression([
        'object' => new class () {
            public string $name = 'John';
        },
        'prop_name' => 'name',
    ]);

    assertEquals($expr->evaluate('object.name'), 'John');
    assertEquals($expr->evaluate('object["name"]'), 'John');
    assertEquals($expr->evaluate("object['name']"), 'John');
    assertEquals($expr->evaluate('object[`name`]'), 'John');
    assertEquals($expr->evaluate('object[prop_name]'), 'John');
    assertEquals($expr->evaluate('object[ prop_name ]'), 'John');
});

test('object method', function () {
    $expr = new Expression([
        'object' => new class () {
            public function method(int $number): int
            {
                return $number * 100;
            }
        },
        'method_name' => 'method',
        'number' => 12
    ]);

    assertEquals($expr->evaluate("object.method(10)"), 1000);
    assertEquals($expr->evaluate("object.method( 10 )"), 1000);
    assertEquals($expr->evaluate("object['method']( 10 )"), 1000);
    assertEquals($expr->evaluate("object[method_name](10)"), 1000);

    assertEquals($expr->evaluate("object.method( number )"), 1200);
    assertEquals($expr->evaluate("object[method_name](number)"), 1200);
});

test('ternary', function () {
    $expr = new Expression();

    assertEquals($expr->evaluate("true?'yes':'no'"), 'yes');
    assertEquals($expr->evaluate("true ? 'yes' : 'no'"), 'yes');

    assertEquals($expr->evaluate("false?'yes':'no'"), 'no');
    assertEquals($expr->evaluate("false ? 'yes' : 'no'"), 'no');
});

test('short ternary', function () {
    $expr = new Expression();

    assertEquals($expr->evaluate("true?'yes'"), 'yes');
    assertEquals($expr->evaluate("true ? 'yes'"), 'yes');

    assertNull($expr->evaluate("false?'yes'"));
    assertNull($expr->evaluate("false ? 'yes'"));


    assertEquals($expr->evaluate("'yes'?:'no'"), 'yes');
    assertEquals($expr->evaluate("'yes' ?: 'no'"), 'yes');

    assertEquals($expr->evaluate("false?:'no'"), 'no');
    assertEquals($expr->evaluate("false ?: 'no'"), 'no');
});

test('null coalesce', function () {

	$expr = new Expression();

	assertEquals($expr->evaluate("null ?? 'yes'"), 'yes');
	assertEquals($expr->evaluate("null ?? null ?? 'no'"), 'no');

	assertEquals($expr->evaluate("null ?? null ?? false ? 'no' : 'yes'"), 'yes');
	assertEquals($expr->evaluate("null ?? null ?? true ? 'no' : 'yes'"), 'no');

	assertEquals($expr->evaluate("null ?? null ?? false?'no':'yes'"), 'yes');
	assertEquals($expr->evaluate("null ?? null ?? true?'no':'yes'"), 'no');
});

test('null coalesce variables', function () {

	$expr = new Expression([
		'a' => null,
		'b' => 'b',
		'c' => null
	]);

	assertEquals($expr->evaluate("a ?? c ?? b"), 'b');
	assertNull($expr->evaluate("a ?? c"));

});

test('constants expression', function () {
	define('TeST_CONTS1', '1');
	define('TeST_CONTS2', '2');
	$expr = new Expression();

	assertEquals($expr->evaluate("TeST_CONTS1"), '1');
	assertEquals($expr->evaluate("TeST_CONTS2"), '2');
	assertEquals($expr->evaluate("TeST_CONTS1 + TeST_CONTS2"), '3');

	assertEquals($expr->evaluate("constant('TeST_CONTS1')"), '1');
	assertEquals($expr->evaluate("constant('TeST_CONTS2')"), '2');
	assertEquals($expr->evaluate("constant('TeST_CONTS1') + constant('TeST_CONTS2')"), '3');
});

test('multi line with result forwarding', function () {
	
	$expr = new Expression();

	assertEquals($expr->evaluate('
		1 + 2;
		_expression_1 + 3;
		_expression_1 + _expression_2 + 5
	'), '14');

	assertEquals($expr->evaluate('
		1 + 2;
		_expression_1 + 3;
		_expression_1 + _expression_2 + 5;
	'), '14');

	assertTrue($expr->evaluate('
		1 + 2;
		_expression_1 + 3;
		_expression_1 + _expression_2 + 5 > 13
	'));

	assertFalse($expr->evaluate('
		1 + 2;
		_expression_1 + 3;
		_expression_1 + _expression_2 + 5 < 13
	'));
	
});

test('spaceship operator', function () {
	$expr = new Expression();
	assertEquals($expr->evaluate('1 <=> 0'), '1');

	assertEquals($expr->evaluate('1 <=> 1'), '0');

	assertEquals($expr->evaluate('0 <=> 1'), '-1');
});

test('when function', function () {
	$expr = new Expression();
	assertEquals($expr->evaluate('when(1 > 2, "yes", "no")'), 'no');

	assertEquals($expr->evaluate('when(3 > 2, "yes", "no")'), 'yes');

	assertEquals($expr->evaluate('when(3 > 2, when(true, "yes1", "no1"), when(true, "yes2", "no2"))'), 'yes1');

	assertEquals($expr->evaluate('when(1 > 2, when(true, "yes1", "no1"), when(false, "yes2", "no2"))'), 'no2');

	assertEquals($expr->evaluate('when(3 > 2, when(false, "yes1", "no1"), when(true, "yes2", "no2"))'), 'no1');

	assertEquals($expr->evaluate('when(1 > 2, when(true, "yes1", "no1"), when(true, "yes2", "no2"))'), 'yes2');

});

test('empty function', function () {
	$expr = new Expression([
		'var1' => [],
		'var2' => [1]
	]);

	assertTrue($expr->evaluate('empty("")'));
	assertTrue($expr->evaluate('empty(0)'));
	assertTrue($expr->evaluate('empty(null)'));
	assertTrue($expr->evaluate('empty(var1)'));

	assertFalse($expr->evaluate('empty(1)'));

	assertTrue($expr->evaluate('!empty(var2)'));

});

test('isset function', function () {
	$expr = new Expression([
		'var1' => null,
		'var2' => 1,
		
	]);
	
	assertTrue($expr->evaluate('!isset(var1)'));
	assertTrue($expr->evaluate('isset(var2)'));
	assertFalse($expr->evaluate('!isset(var2)'));

});

test('is_null function', function () {
	$expr = new Expression([
		'var1' => null,
		'var2' => 1,
		
	]);
	
	assertTrue($expr->evaluate('is_null(var1)'));
	assertTrue($expr->evaluate('!is_null(var2)'));
	assertFalse($expr->evaluate('is_null(var2)'));

});

test('in function', function () {
	$expr = new Expression([
		'age' => 5,
		'range' => range(1, 2)
	]);
	
	assertTrue($expr->evaluate('in(age, [1, 2, 3, 4, 5, 6, 7, 10])'));
	assertTrue($expr->evaluate('!in(age, range)'));

	assertFalse($expr->evaluate('in(age, range)'));

});

test('contains function', function () {
	$expr = new Expression([
		'stringTest' => 'allen joel',
	]);
	
	assertTrue($expr->evaluate('contains(stringTest, "allen")'));
	assertTrue($expr->evaluate('contains(stringTest, "joel")'));

	assertFalse($expr->evaluate('contains(stringTest, "joela")'));

});

test('starts_with function', function () {
	$expr = new Expression([
		'stringTest' => 'allen joel',
	]);
	
	assertTrue($expr->evaluate('starts_with(stringTest, "allen")'));

	assertFalse($expr->evaluate('starts_with(stringTest, "joel")'));
	assertFalse($expr->evaluate('starts_with(stringTest, "allena")'));

});

test('ends_with function', function () {
	$expr = new Expression([
		'stringTest' => 'allen joel',
	]);
	
	assertTrue($expr->evaluate('ends_with(stringTest, "joel")'));

	assertFalse($expr->evaluate('ends_with(stringTest, "allen")'));
	assertFalse($expr->evaluate('ends_with(stringTest, "joela")'));

});

test('function forwarding', function () {
    $expr = new Expression([
        'test1' => 'a'
    ]);
    
    assertTrue($expr->evaluate('1 + 1 -> in($$, [1,2]) -> !empty($$)'));
    assertTrue($expr->evaluate('"test_" ~ test1  -> ends_with($$, "a") -> !empty($$)'));
    assertTrue($expr->evaluate('2 >= 2 -> !empty($$)'));

    assertFalse($expr->evaluate('1 >= 2 -> !empty($$)'));
    assertFalse($expr->evaluate('"test_" ~ test1  -> ends_with($$, "b") -> !empty($$)'));
    assertFalse($expr->evaluate('1 + 2 -> in($$, [1,2]) -> !empty($$)'));

});