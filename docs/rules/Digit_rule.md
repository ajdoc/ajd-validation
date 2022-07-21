# Digit

- `digit()`
- `Digit_rule()`

Validates whether the input is a digit.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->digit()->check('digit_field', 'a'); // will put error in error bag
$v->getValidator()->digit()->validate('a'); // false

$v->digit()->check('digit_field', '1'); // validation passes
$v->getValidator()->digit()->validate(1); // true

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
