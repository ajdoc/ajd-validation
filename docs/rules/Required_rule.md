# Required

- `required()`
- `Required_rule($minAge = NULL, $maxAge = NULL, $inclusive = true)`

Validates whether the input is not empty.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->required()->check('required_field', ''); // will put error in error bag
$v->getValidator()->required()->validate(''); // false

$v->required()->check('required_field', null); // will put error in error bag
$v->getValidator()->required()->validate(null); // false

$v->required()->check('required_field', 0); // will put error in error bag
$v->getValidator()->required()->validate(0); // false

$v->required()->check('required_field', '0'); // will put error in error bag
$v->getValidator()->required()->validate('0'); // false

$v->required()->check('required_field', []); // will put error in error bag
$v->getValidator()->required()->validate([]); // false


$v->required()->check('required_field', 1); // validation passes
$v->getValidator()->required()->validate(1); // true

$v->required()->check('required_field', 'hello world'); // validation passes
$v->getValidator()->required()->validate('hello world'); // true

$v->required()->check('required_field', ['hello_world']); // validation passes
$v->getValidator()->required()->validate(['hello_world']); // true

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
