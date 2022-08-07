# Dns email

- `dns_email()`
- `Dns_email_rule()`


Validates whether the input's value is a valid email uses \Egulias\EmailValidator\Validation\DNSCheckValidation.


```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->dns_email()->check('email_field', 'a'); // will put error in error bag
$v->getValidator()->dns_email()->validate('a'); // false

$v->dns_email()->check('email_field', 'test@test.com'); // will put error in error bag
$v->getValidator()->dns_email()->validate('test@test.com'); // false

$v->dns_email()->check('email_field', 'johndoe@yopmail.com'); // validation passes
$v->getValidator()->dns_email()->validate('johndoe@yopmail.com'); // true

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release, Also uses "egulias/emailvalidator" for email validation

***
