# Leap year

- `lead_year()`
- `Leap_year_rule()`

Validates the input value is a valid Leap year.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->leap_year()->check('leap_year_field', '2021'); // will put error on error bag
$v->getValidator()->leap_year()->validate('2021'); // false

$v->leap_year()->check('leap_year_field', '2020'); // validation passes
$v->getValidator()->leap_year()->validate('2020'); // true

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
