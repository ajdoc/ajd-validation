# Usage

In this document we'll see how to use ajd-validation

```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;


	$v
		->required()
		->minlength(5)
		->check('firstname', 'value-of-firstname');

	$v
		->required()
		->minlength(5)
		->check('lastname', 
			[
				'lastname' => 'value-of-lastname'
			]
		);

	if($v->validation_fails())
	{
		var_dump($v->errors()->all());
		echo $v->errors()->toStringErr();
	}

```

You define rules by chainging like in the example after you define all the rules you can start validation by calling check method which receives the field key or field name as the first paramater and the value or array of values organized as an associative array like in the example above as the second paramater.

If the validation fails, validation_fails method will return true, error messages can be retrieved using `$v->errors()->all()` which will return an associative array of field and rules error messages or you can use `$v->errors()->toStringErr()` which will return formated error messages.