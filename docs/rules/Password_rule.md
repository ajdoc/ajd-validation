# Password

- `password()`
- `Password_rule(Rule_interface ...$rules)`

An example of compounding a set of rules into a rule class.
- Current rules are 
```php
$validator1 = $this->getValidator()
            ->required()
            ->minlength(5, true, true)
            ->alnum();

$validator2 = $this->getValidator()
				->uncompromised();
```
- This means that $validator2 will only run if all the rules in $validator1 passes.


```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->password()->check('compound_password', ''); // validation fails
/*
	Outputs error
	All of the required rules must pass for "Compound password".
	  - All of the rules must pass for Compound password. 
	    - The "Compound password" field is required
	    - "Compound password" must be greater than or equal to 5.
	    - "Compound password" must contain only letters (a-z) and digits (0-9). 
*/


$v->getValidator()->password()->validate(''); // false


$v->password()->check('compound_password', 'a1235'); // validation passes
$v->getValidator()->password()->validate('a1235'); // true


```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
