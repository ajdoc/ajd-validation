# Filtervar

- `filtervar()`
- `Filtervar_rule(int $filter, mixed $options)`

Validates the input value with PHP's filter_var() function.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->filtervar(FILTER_VALIDATE_URL)->check('filtervar_field', 'a@test.com'); // will put error in error bag
$v->getValidator()->filtervar(FILTER_VALIDATE_URL)->validate('a@test.com'); // false

$v->filtervar(FILTER_VALIDATE_EMAIL)->check('filtervar_field', 'http://test.com'); // will put error in error bag
$v->getValidator()->filtervar(FILTER_VALIDATE_EMAIL)->validate('http://test.com'); // false

$v->filtervar(FILTER_VALIDATE_URL)->check('filtervar_field', 'http://test.com'); // validation passes
$v->getValidator()->filtervar(FILTER_VALIDATE_URL)->validate('http://test.com'); // true

$v->filtervar(FILTER_VALIDATE_EMAIL)->check('filtervar_field', 'a@test.com'); // validation passes
$v->getValidator()->filtervar(FILTER_VALIDATE_EMAIL)->validate('a@test.com'); // true

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
