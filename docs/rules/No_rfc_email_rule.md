# No Rfc email

- `no_rfc_email()`
- `No_rfc_email_rule()`


Validates whether the input's value is a valid email uses \Egulias\EmailValidator\Validation\NoRFCWarningsValidation.


```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->no_rfc_email()->check('email_field', 'a'); // will put error in error bag
$v->getValidator()->no_rfc_email()->validate('a'); // false

$v->no_rfc_email()->check('email_field', 'johndoe@yopmail.com'); // validation passes
$v->getValidator()->no_rfc_email()->validate('johndoe@yopmail.com'); // true

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release, Also uses "egulias/emailvalidator" for email validation

***
