# Date

- `date()`
- `Date_rule($format = null)`

Validates whether the input value is a valid date.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->date('Y-m-d')->check('date_field', '2019-02-29'); // will put error in error bag
$v->getValidator()->date('Y-m-d')->validate('2019-02-29'); // false

$v->date('Y-m-d')->check('date_field', '2022-01-01'); // validation passes
$v->getValidator()->date('Y-m-d')->validate('2022-01-01'); // true

$v->date('F jS, Y')->check('date_field', 'March 1st, 1992'); // validation passes
$v->getValidator()->date('F jS, Y')->validate('March 1st, 1992'); // true

$v->date('m/d/y')->check('date_field', '03/01/95'); // validation passes
$v->getValidator()->date('m/d/y')->validate('03/01/95'); // true

$v->date('Ydm')->check('date_field', 20220101); // validation passes
$v->getValidator()->date('Ydm')->validate(20220101); // true

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
