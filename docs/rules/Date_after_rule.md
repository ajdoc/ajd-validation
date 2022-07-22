# Date After

- `date_after()`
- `Date_after_rule([$compareDate, $dateFormat, $inclusive = false])`

Validates whether the input value is a valid date after the given compare date.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->date_after(['2022-03-02', 'Y-m-d'])->check('date_after_field', '2022-03-01'); // will put error in error bag
$v->getValidator()->date_after(['2023-03-02', 'Y-m-d'])->validate('2023-03-01') // false

$v->date_after(['2022-03-02', 'Y-m-d'])->check('date_after_field', '2022-03-02'); // validation passes
$v->getValidator()->date_after(['2023-03-02', 'Y-m-d'])->validate('2023-03-03') // true

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
