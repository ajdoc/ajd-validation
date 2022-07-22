# Tld

- `tld()`
- `Tld_rule()`

Validates whether the input is a top-level domain.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->tld()->check('tld_field', 'coa'); // will put error in error bag
$v->getValidator()->tld()->validate('coa'); // false

$v->tld()->check('tld_field', 'com'); // validation passes
$v->getValidator()->tld()->validate('com'); // true

$v->tld()->check('tld_field', 'org'); // validation passes
$v->getValidator()->tld()->validate('org'); // true

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
