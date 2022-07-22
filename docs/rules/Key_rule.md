# Key

- `key()`
- `Key_rule($relation, Rule_interface $referenceValidator, $mandatory = true)`

Validates the input value array key.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$arr1 = ['foo' => 'bar'];
$arr2 = ['key_field' => ['foo' => 'bar']];

/*
	Third paramater in ->checks('key_field', $arr2, false) disables auto array checking
*/
$v->key('fooa')->check('key_field', $arr2, false); // will put error in error bag
$v->getValidator()->key('fooa')->validate($arr1); // false

$v->key('foo')->check('key_field', $arr2, false); // validation passes
$v->getValidator()->key('foo')->validate($arr1); // true

$arr1 = ['foo' => ''];
$arr2 = ['key_field' => ['foo' => '']];

$v->key('foo', $v->getValidator()->required()->digit())->check('key_field', $arr2, false); // will put error in error bag
$v->getValidator()->key('foo', $v->getValidator()->required()->digit())->validate($arr1); // false

/*
	Outputs Error
	All of the required rules must pass for "Key field".
	  - Key Key field must be present. 
	    - Key "foo" must be valid.
	    - The "foo" field is required
	    - "foo" must contain only digits (0-9)..
*/

$arr1 = ['foo' => 1];
$arr2 = ['key_field' => ['foo' => '1']];

$v->key('foo', $v->getValidator()->required()->digit())->check('key_field', $arr2, false); // validation passes
$v->getValidator()->key('foo', $v->getValidator()->required()->digit())->validate($arr1); // true

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
