# Length

- `length()`
- `length_rule(int $minValue = null, int $maxValue = null, $inclusive = true)`

Validates the input's length.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

/*
	Third paramater in ->checks('length_field', [1,2,3], false) disables auto array checking
*/
$v->length(1, 3)->check('length_field', [1,2,3], false); // validation passes
$v->getValidator()->length(1, 3)->validate([1,2,3]); // true

$v->length(1, 3)->check('length_field', [1,2,3,4], false); // will put error on error bag
$v->getValidator()->length(1, 3)->validate([1,2,3,4]); // false

$v->length(1, 3)->check('length_field', 'aaa'); // validation passes
$v->getValidator()->length(1, 3)->validate('aaa'); // true

$v->length(2)->check('length_field', 'aaa'); // validation passes
$v->getValidator()->length(2)->validate('aaa'); // true

$v->length(2)->check('length_field', 'a'); // will put error on error bag
$v->getValidator()->length(2)->validate('a'); // false

$v->length(null, 3)->check('length_field', 'aaa'); // validation passes
$v->getValidator()->length(null, 3)->validate('aaa'); // true

$v->length(null, 4)->check('length_field', 'aaaaa'); // will put error on error bag
$v->getValidator()->length(null, 4)->validate('aaaaa'); // false

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
