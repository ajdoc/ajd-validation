# Key Nested

- `key_nested()`
- `Key_nested_rule($relation, Rule_interface $referenceValidator, $mandatory = true)`

Validates the input value array key or an object property using . to represent nested data.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$arr1 = ['foo' => ['bar' => '']];
$arr2 = ['key_nested_field' => ['foo' => ['bar' => '']]];

/*
	Third paramater in ->checks('key_field', $arr2, false) disables auto array checking
*/
$v->key_nested('foo.bara')->check('key_nested_field', $arr2, false); // will put error in error bag
$v->getValidator()->key_nested('foo.bara')->validate($arr1); // false

$v->key_nested('foo.bar')->check('key_nested_field', $arr2, false); // validation passes
$v->getValidator()->key_nested('foo.bar')->validate($arr1); // true

$v->key_nested('foo.bar', $v->getValidator()->required()->digit())->check('key_nested_field', $arr2, false); // will put error in error bag
$v->getValidator()->key_nested('foo.bar', $v->getValidator()->required()->digit())->validate($arr1); // false
/*
	Outputs Error
	All of the required rules must pass for "Key nested field".
	  - No items were found for key chain Key nested field. 
	    - Key chain "foo.bar" is not valid.
	    - The "foo.bar" field is required
	    - "foo.bar" must contain only digits (0-9)..
*/

$arr1 = ['foo' => ['bar' => 1]];
$arr2 = ['key_nested_field' => ['foo' => ['bar' => '1']]];

$v->key_nested('foo.bar', $v->getValidator()->required()->digit())->check('key_nested_field', $arr2, false); // validation passes
$v->getValidator()->key_nested('foo.bar', $v->getValidator()->required()->digit())->validate($arr1); // true

$object = new stdClass;
$arr1 = $object;
$arr1->foo = (new stdClass);
$arr1->foo->bar = '1';

$v->key_nested('foo.bar', $v->getValidator()->required()->digit())->check('key_nested_field', $arr1, false); // validation passes
$v->getValidator()->key_nested('foo.bar', $v->getValidator()->required()->digit())->validate($arr1); // true

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
