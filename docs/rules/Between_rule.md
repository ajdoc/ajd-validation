# Between

- `between()`
- `Between_rule($minValue = NULL, $maxValue = NULL, $inclusive = true)`

Validates whether the input is between two other values.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->between(10, 20)->check('between_field', 9); // will put error in error bag
$v->getValidator()->between(10, 20)->validate(9); // false

$v->between(10, 20)->check('between_field', 10); // validation passes
$v->getValidator()->between(10, 20)->validate(11); // true

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
