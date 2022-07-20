# Email

- `email()`
- `Email_rule($emailChecker = NULL)`

Validates whether the input's value is a valid email.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->email()->check('email_value', 'a'); // will put error in error bag
$v->getValidator()->email()->validate('a'); // false

$v->email()->check('email_value', 'johndoe@yopmail.com'); // validation passes
$v->getValidator()->email()->validate('johndoe@yopmail.com'); // true

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release, Also uses "egulias/emailvalidator" for email validation

***
