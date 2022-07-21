# Different

- `different()`
- `Different_rule($compareTo, $identical = false)`

Validates whether the input's value is different from the compare value.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->different('a')->check('different_field', 'a'); // will put error in error bag
$v->getValidator()->different('a')->validate('a'); // false

$v->different('a')->check('different_field', 's'); // validation passes
$v->getValidator()->different('a')->validate('s'); // true

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
