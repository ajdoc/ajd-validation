# Compare

- `compare()`
- `Compare_rule($comparator, $compareValue = "", $toString = true)`
- `valid $comparator ['==', '===', '!=', '!==', '<>', '<', '>', '<=', '>=', '<=>']`

Validates whether the input's value is valid thru php's comparison operators.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->compare('==', 'b')->check('compare_field', 'a'); // will put error in error bag
$v->getValidator()->compare('==', 'b')->validate('a'); // false

$v->compare('==', 'b')->check('compare_field', 'b'); // validation passes
$v->getValidator()->compare('==', 'b')->validate('b'); // true

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
