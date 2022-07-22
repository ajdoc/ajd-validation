# Type

- `type()`
- `Type_rule(string $type)`
- allowed types 
	- [
		'array' 		=> 'array',
		'bool' 			=> 'boolean',
		'boolean' 		=> 'boolean',
		'callable' 		=> 'callable',
		'double' 		=> 'double',
		'float' 		=> 'double',
		'int' 			=> 'integer',
		'integer' 		=> 'integer',
		'null' 			=> 'NULL',
		'object' 		=> 'object',
		'resource' 		=> 'resource',
		'string' 		=> 'string'
	];

Validates the type of the input.

```php
use AJD_validation\AJD_validation as v;

$v = new v;


$v->type('callable')->check('type_rule', new \AJD_validation\Rules\Invokable_required_rule());  // validation passes
$v->getValidator()->type('callable')->validate(new \AJD_validation\Rules\Invokable_required_rule()); // true

$v->type('callable')->check('type_rule', function(){});  // validation passes
$v->getValidator()->type('callable')->validate(function(){}); // true

$v->type('callable')->check('type_rule', 'is_string');  // validation passes
$v->getValidator()->type('callable')->validate('is_string'); // true

/*
	Third paramater false disables auto array checking
*/
$v->type('callable')->check('type_rule', [new SomeClass, 'someMethod'], false);  // validation passes
$v->getValidator()->type('callable')->validate([new SomeClass, 'someMethod']); // true


$v->type('object')->check('type_rule', new StdClass);  // validation passes
$v->getValidator()->type('object')->validate(new StdClass); // true

$v->type('bool')->check('type_rule', false);  // validation passes
$v->getValidator()->type('bool')->validate(false); // true

$v->type('bool')->check('type_rule', true);  // validation passes
$v->getValidator()->type('bool')->validate(true); // true

$v->type('double')->check('type_rule', 1.00);  // validation passes
$v->getValidator()->type('double')->validate(1.00); // true

$v->type('float')->check('type_rule', 1.00);  // validation passes
$v->getValidator()->type('float')->validate(1.00); // true


```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
