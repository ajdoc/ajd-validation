# Phone

- `phone()`
- `Phone_rule()`

Validates the input value is a valid philippines telephone number format.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->phone()->check('phone_field', '721-15-25'); // will put error on error bag
$v->getValidator()->phone()->validate('722-15-25'); // false

$v->phone()->check('phone_field', '721-1525'); // validation passes
$v->getValidator()->phone()->validate('721-1525'); // true

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
