# Date Before

- `date_before()`
- `Date_before_rule([$compareDate, $dateFormat, $inclusive = false])`

Validates whether the input value is a valid date before the given compare date.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->date_before(['2022-03-02', 'Y-m-d'])->check('date_before_field', '2022-03-03'); // will put error in error bag
$v->getValidator()->date_before(['2023-03-02', 'Y-m-d'])->validate('2023-03-03') // false

$v->date_before(['2022-03-02', 'Y-m-d'])->check('date_before_field', '2022-03-01'); // validation passes
$v->getValidator()->date_before(['2023-03-02', 'Y-m-d'])->validate('2023-03-02') // true

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
