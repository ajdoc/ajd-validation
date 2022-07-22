# Uncompromise

- `uncompromise()`
- `Uncompromise_rule($threshold = 0, $checkType = 'pwned')`
- Uses https://api.pwnedpasswords.com/range/ for checking
- $threshold - how many times a password allowed to be a part of data leak before validation fails
- $checkType - 'pwned' - uses \AJD_validation\Uncompromised\NotPawnedVerifier::class

Validates whether the input password has been in a part of a data leak.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->uncompromised()->check('uncompromised_field', 'admin'); // will put error in error bag
$v->getValidator()->uncompromised()->validate('admin'); // false

$v->uncompromised()->check('uncompromised_field', 'adminfoobar');  // validation passes
$v->getValidator()->uncompromised()->validate('adminfoobar'); // true

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
