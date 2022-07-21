# Identical

- `identical()`
- `Identical_rule($compareto)`

Validates whether the input is equal to some value.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->identical(1)->check('identical_field', '1'); // will put error in error bag
$v->getValidator()->identical(1)->validate('1'); // false

$v->identical(1)->check('identical_field', 1); // validation passes
$v->getValidator()->identical(1)->validate(1); // true
```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
