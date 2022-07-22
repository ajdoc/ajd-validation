# No Whitespace

- `no_whitespace()`
- `No_whitespace_rule()`

Validates the input value has a whitespace.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->no_whitespace()->check('no_whitespace_field', 'foo b a   r'); // will put error on error bag
$v->getValidator()->no_whitespace()->validate('foo b a   r'); // false

$v->no_whitespace()->check('no_whitespace_field', 'foobar'); // validation passes
$v->getValidator()->no_whitespace()->validate('foobar'); // true

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
