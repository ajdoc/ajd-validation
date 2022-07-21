# Vowel

- `vowel()`
- `Vowel_rule($additionalChars)`

Validates whether the input contains only vowel.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->vowel()->check('vowel_field', 's'); // will put error in error bag
$v->getValidator()->vowel()->validate('s'); // false

$v->vowel()->check('vowel_field', 'a'); // validation passes
$v->getValidator()->consonant()->validate('a'); // true
```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
