# Alnum

- `alpha()`
- `Alnum_rule($additionalChars)`

Validates whether the input is alphanumeric or not. Alphanumeric is a combination of alphabetic (a-z and A-Z) and numeric (0-9) characters.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->alnum()->check('alnum_rule', 'test 12'); // will put error in error bag
$v->getValidator()->alnum()->validate('test 12'); // false

$v->alnum(' ')->check('alnum_rule', 'test 12'); // validation passes
$v->getValidator()->alnum(' ')->validate('test 12'); // true

$v->alnum()->check('alnum_rule', '100%'); // will put error in error bag
$v->getValidator()->alnum()->validate('100%'); // false

$v->alnum('%')->check('alnum_rule', '100%'); // validation passes
$v->getValidator()->alnum('%')->validate('100%'); // true

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
