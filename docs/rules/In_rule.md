# In

- `in()`
- `In_rule($haystack = null, $identical)`

Validates whether the input is contained in a specific haystack.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$arr = [
	1, 2
];

$v->in($arr)->check('in_field', 3); // will put error in error bag
$v->getValidator()->in($arr)->validate(3); // false

$v->in($arr)->check('in_field', 2); // validation passes
$v->getValidator()->in($arr)->validate(2); // true

$v->in('lorem ipsum')->check('in_field', 'test'); // will put error in error bag
$v->getValidator()->in('lorem ipsum')->validate('test'); // false

$v->in('lorem ipsum')->check('in_field', 'lorem'); // validation passes
$v->getValidator()->in('lorem ipsum')->validate('lorem'); // true

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
