# Distinct

- `distinct()`
- `Distinct_rule($arrayOfData = [])`

Validates whether the input is unique inside a one dimensional array.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$arr = [
	'distinct_field' => [1,1]
];

$v->distinct($arr)->check('distinct_field', $arr); // will put error in error bag
$v->getValidator()->distinct()->validate([1,1]); // false

$arr2 = [
	'distinct_field' => [1,2]
];

$v->distinct($arr2)->check('distinct_field', $arr2); // validation passes
$v->getValidator()->distinct()->validate([1,2]); // true

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
