# Digit

- `digit_count()`
- `Digit_count_rule(int $digitLength)`

Validates whether the input value digit length.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->digit_count(1)->check('digit_count_field', 90); // will put error in error bag
$v->getValidator()->digit_count()->validate(90); // false

$v->digit_count(1)->check('digit_count_field', '1'); // validation passes
$v->getValidator()->digit_count(1)->validate(1); // true

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
