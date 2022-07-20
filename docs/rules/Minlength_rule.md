# Required

- `minlength()`
- `Minlength_rule($length = 0, $inclusive = true, $isString = false)`

Validates whether the input greater is than or equal to a value.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->minlength(10)->check('minlength_rule',9); // will put error in error bag
$v->getValidator()->minlength(10)->validate(9); // false

$v->minlength(10)->check('minlength_rule',10); // validation passes
$v->getValidator()->minlength(10)->validate(10); // true

// forced value to be string to make the checking string length
$v->minlength(2, true, true)->check('minlength_rule','a'); // will put error in error bag
$v->getValidator()->minlength(2)->validate('aa'); // true

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
