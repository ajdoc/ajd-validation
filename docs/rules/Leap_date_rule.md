# Leap date

- `lead_date()`
- `Leap_date_rule()`

Validates the input value is a valid Leap year date.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->leap_date()->check('leap_date_field', '2021-02-29'); // will put error on error bag
$v->getValidator()->leap_date()->validate('2021-02-29'); // false

$v->leap_date()->check('leap_date_field', '2020-02-29'); // validation passes
$v->getValidator()->leap_date()->validate('2020-02-29'); // true

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
