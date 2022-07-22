# Mobile No

- `mobileno()`
- `mobileno_rule(string $additionalChars, $startNo = false)`
- Philippines only mobile no format.
- if $startNo = true must start with +639

Validates whether the input value is valid mobile no format in the philippines.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->mobileno()->check('mobileno_field', '90'); // will put error in error bag
$v->getValidator()->mobileno()->validate(90); // false

$v->mobileno()->check('mobileno_field', '09257981924'); // validation passes
$v->getValidator()->mobileno()->validate('09257981924'); // true

$v->mobileno()->check('mobileno_field', '9257981924'); // validation passes
$v->getValidator()->mobileno()->validate('9257981924'); // true

$v->mobileno(null, true)->check('mobileno_field', '+639257981924'); // validation passes
$v->getValidator()->mobileno(null, true)->validate('+639257981924'); // true

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
