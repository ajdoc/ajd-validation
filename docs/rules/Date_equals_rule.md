# Date Equals

- `date_equals()`
- `Date_equals_rule([$compareDate, $dateFormat])`

Validates whether the input value is a valid date equals to the given compare date.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->date_equals(['2022-03-02', 'Y-m-d'])->check('date_equals_field', '2022-03-03'); // will put error in error bag
$v->getValidator()->date_equals(['2023-03-02', 'Y-m-d'])->validate('2023-03-03') // false

$v->date_equals(['2022-03-02', 'Y-m-d'])->check('date_equals_field', '2022-03-02'); // validation passes
$v->getValidator()->date_equals(['2023-03-02', 'Y-m-d'])->validate('2023-03-02') // true

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
