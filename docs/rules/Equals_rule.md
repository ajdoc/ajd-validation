# Equals

- `equals()`
- `Equals_rule($compareto)`

Validates whether the input is equal to some value.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->equals('lorem')->check('equals_field', 'ipsum'); // will put error in error bag
$v->getValidator()->equals('lorem')->validate('ipsum'); // false

$v->equals('lorem')->check('equals_field', 'lorem'); // validation passes
$v->getValidator()->equals('lorem')->validate('lorem'); // true
```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
