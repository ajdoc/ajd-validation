# Multiple

- `multiple()`
- `Multiple_rule(int $multipleof)`

Validates the input value is a multiple of the given parameter.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->multiple(2)->check('multiple_field', '3'); // will put error on error bag
$v->getValidator()->multiple(2)->validate(3); // false

$v->multiple(2)->check('multiple_field', 4); // validation passes
$v->getValidator()->multiple(2)->validate('4'); // true

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
