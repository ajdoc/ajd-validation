# Key Value

- `key_value()`
- `Key_value_rule(array $satisfier = [])`

Performs validation of field key using the rule named on 'rule_name'. Usually used for password and password_confirm.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$arr1 = ['password' => '1', 'password_confirm' => '1a'];
$arr2 = ['password' => 'ssa', 'password_confirm' => 'ss'];

/*
	Third paramater in ->checks('key_field', $arr2, false) disables auto array checking
*/
$v->key_value(['password_confirm', 'equals', 'password'])->check('password_confirm', $arr2, false); // will put error in error bag
$v->getValidator()->key_value(['password_confirm', 'equals', 'password'])->validate($arr1); // false

/*
	Outputs Error
	All of the required rules must pass for "Password confirm".
  		- "Password confirm" must be equals to "ssa".
*/

$arr1 = ['password' => '1', 'password_confirm' => '1'];
$arr2 = ['password' => 'ssa', 'password_confirm' => 'ssa'];

$v->key_value(['password_confirm', 'equals', 'password'])->check('password_confirm', $arr2, false); // validation passes
$v->getValidator()->key_value(['password_confirm', 'equals', 'password'])->validate($arr1); // true


```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
