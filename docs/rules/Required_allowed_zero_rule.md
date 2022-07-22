# Required allowed zero

- `required_allowed_zero()`
- `Required_allowed_zero_rule()`

Validates whether the input is not empty but allows zero value.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->required_allowed_zero()->check('required_allowed_zero_field', null);  // will put error in error bag
$v->getValidator()->required_allowed_zero()->validate(null); // false

$v->required_allowed_zero()->check('required_allowed_zero_field', '');  // will put error in error bag
$v->getValidator()->required_allowed_zero()->validate(''); // false

$v->required_allowed_zero()->check('required_allowed_zero_field', '0');  // validation passes
$v->getValidator()->required_allowed_zero()->validate('0'); // true

$v->required_allowed_zero()->check('required_allowed_zero_field', 0);  // validation passes
$v->getValidator()->required_allowed_zero()->validate(0); // true

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
