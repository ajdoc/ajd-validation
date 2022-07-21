# Odd

- `odd()`
- `Odd_rule()`

Validates whether the input is an odd number.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->odd()->check('odd_field', 2); // will put error in error bag
$v->getValidator()->odd()->validate(2); // false

$v->odd()->check('odd_field', 1); // validation passes
$v->getValidator()->odd()->validate(1); // true
```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
