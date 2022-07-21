# Consonant

- `consonant()`
- `Consonant_rule($additionalChars)`

Validates whether the input contains only consonants.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->consonant()->check('consonant_field', 'a'); // will put error in error bag
$v->getValidator()->consonant()->validate('a'); // false

$v->consonant()->check('consonant_field', 's'); // validation passes
$v->getValidator()->consonant()->validate('s'); // true

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
