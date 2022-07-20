# Contains

- `contains()`
- `Contains_rule($haystack = null, $identical = null)`

Validates whether the input's value contains some value..

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->contains('ss')->check('contains_value', 'a'); // will put error in error bag
$v->getValidator()->contains('ss')->validate('a'); // false

$v->contains('ss')->check('contains_value', 'sss'); // validation passes
$v->getValidator()->contains('ss')->validate('sss'); // true

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
