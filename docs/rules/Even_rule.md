# Even

- `even()`
- `Even_rule()`

Validates whether the input is an even number.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->even()->check('even_field', 1); // will put error in error bag
$v->getValidator()->even()->validate(1); // false

$v->even()->check('even_field', 2); // validation passes
$v->getValidator()->even()->validate(2); // true
```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
