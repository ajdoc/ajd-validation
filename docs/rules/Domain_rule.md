# Domain

- `domain()`
- `Domain_rule($tldCheck = true)`

Validates whether the input value is a valid domain name or not.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->domain()->check('domain_field', 'google .com'); // will put error in error bag
$v->getValidator()->domain()->validate('google .com'); // false

$v->domain()->check('domain_field', 'google.com'); // validation passes
$v->getValidator()->domain()->validate('google.com'); // true

$v->domain(false)->check('domain_field', 'dev.machine.local'); // validation passes
$v->getValidator()->domain(false)->validate('dev.machine.local'); // true
```

### Validates several rules internally
	* If input is an IP address, it fails.
	* If input contains whitespace, it fails.
	* If input does not contain any dots, it fails.
	* If input has less than two parts, it fails.
	* Input must end with a top-level-domain to pass (if not skipped).
	* Each part must be alphanumeric and not start with an hyphen.

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
