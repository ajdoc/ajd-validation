# Email

- `email()`
- `Email_rule($ruleOptions = NULL)`
- Compounded Rules
- default $ruleOptions = [
	'showSubError' => false, // if true will show sub rules sub errors
	'useDns' =>  false // if true will use \Egulias\EmailValidator\Validation\DNSCheckValidation
];
- current compounded rules 
```php
$validator1 = $this->getValidator()
				->base_email()
				->rfc_email()
				->spoof_email();


if($validator1 && $this->ruleOptions['useDns'])
{
	$validator2 = $this->getValidator()->dns_email();
}
```

Validates whether the input's value is a valid email.


```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->email()->check('email_field', 'a'); // will put error in error bag
$v->getValidator()->email()->validate('a'); // false

$v->email()->check('email_field', 'johndoe@yopmail.com'); // validation passes
$v->getValidator()->email()->validate('johndoe@yopmail.com'); // true

```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release, Also uses "egulias/emailvalidator" for email validation

***
