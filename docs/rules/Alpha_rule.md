# Alpha

- `alpha()`
- `Alpha_rule($additionalChars)`

Validates whether the input contains only alphabetic characters.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->alpha()->check('alpha_rule', 'some string'); // will put error in error bag
$v->getValidator()->alpha()->validate('some string'); // false

$v->alpha(' ')->check('alpha_rule', 'some string'); // validation passes
$v->getValidator()->alpha(' ')->validate('some name'); // true

$v->alpha()->check('alpha_rule', 'slug-test'); // will put error in error bag
$v->getValidator()->alpha()->validate('slug-test'); // false

$v->alpha('-')->check('alpha_rule', 'slug-test'); // validation passes
$v->getValidator()->alpha('-')->validate('slug-test'); // true

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
