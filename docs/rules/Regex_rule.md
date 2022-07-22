# Regex

- `regex()`
- `Regex_rule(string $regex)`

Validates the input value matches a defined regular expression.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->regex('[a-z]')->check('regex_field', '1'); // will put error on error bag
$v->getValidator()->regex('[a-z]')->validate('1'); // false

$v->regex('[a-z]')->check('regex_field', 'a'); // validation passes
$v->getValidator()->regex('[a-z]')->validate('a'); // true

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
