# Null Type

- `null_type()`
- `Null_type_rule()`

Validates the input value is a valid numeric value.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->null_type()->check('null_type_field', ''); // will put error on error bag
$v->getValidator()->null_type()->validate(''); // false

$v->null_type()->check('null_type_field', null); // validation passes
$v->getValidator()->null_type()->validate(null); // true

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
