# Required

- `maxlength()`
- `Maxlength_rule($length = 0, $inclusive = true, $isString = false)`

Validates whether the input is less than or equal to a value.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->maxlength(10)->check('minlength_rule',11); // will put error in error bag
$v->getValidator()->maxlength(10)->validate(11); // false

$v->maxlength(10)->check('minlength_rule',9); // validation passes
$v->getValidator()->maxlength(10)->validate(9); // true

// forced value to be string to make the checking string length
$v->maxlength(2, true, true)->check('minlength_rule','aaa'); // will put error in error bag
$v->getValidator()->maxlength(2)->validate('aa'); // true

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
