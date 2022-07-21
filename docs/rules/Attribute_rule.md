# Attribute

- `attribute()`
- `Attribute_rule($relation, Rule_interface $referenceValidator = NULL, $mandatory = true)`

Validates whether the input's object attribute.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$obj = new stdClass;
$obj->test = 'test';

$v->attribute('test1')->check('attribute_field', $obj); // will put error in error bag
$v->getValidator()->->attribute('test1')->validate($obj); // false

$v->attribute('test')->check('attribute_field', $obj); // validation passes
$v->getValidator()->->attribute('test')->validate($obj); // true

$obj = new stdClass;
$obj->test = '';
$v->attribute(
	'test', 
	$v->getValidator()->required()->digit()
)->check('attribute_field', $obj); // will put error in error bag
/* will output this error
All of the required rules must pass for "Attribute field".
  - Attribute Attribute field must be present. 
    - Attribute "test" must be valid.
    - The "test" field is required
     - "test" must contain only digits (0-9)
*/
$v->getValidator()->attribute('test',$v->getValidator()->required()->digit())->validate($obj);  // false

$obj = new stdClass;
$obj->test = '2';
$v->attribute(
	'test', 
	$v->getValidator()->required()->digit()
)->check('attribute_field', $obj); // validation passes

$v->getValidator()->attribute('test',$v->getValidator()->required()->digit())->validate($obj);  // true

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
