# Age

- `age()`
- `Age_rule($minAge = NULL, $maxAge = NULL, $inclusive = true)`

Validates whether the input's age met the minimum age or is between minimum age or maximum age.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->age(12)->check('age_field', 11); // will put error in error bag
$v->getValidator()->age(12)->validate(11); // false

$v->age(12)->check('age_field', 12); // validation passes
$v->getValidator()->age(12)->validate(12); // true

$v->age(12, 14)->check('age_field', 15); // will put error in error bag
$v->getValidator()->age(12)->validate(15); // false

$v->age(12, 14)->check('age_field', 13); // validation passes
$v->getValidator()->age(12)->validate(14); // true

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
