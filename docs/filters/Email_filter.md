# Email

- `Femail()`
- `Email_filter()`

Filter sanitize a value using php `filter_var($value, FILTER_SANITIZE_EMAIL` function.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->Femail(null, true)->check('field', 'john(.doe)@exa//mple.com');
$v->Femail()->check('field', 'a@test.com');

$pre_filter = $v->pre_filter_value();
/*
	returns array;
	array(1) {
	  ["field"]=>
  		string(20) "john.doe@example.com"
	  
	}
*/
$filtered_values = $v->filter_value();

/*
	returns array;
	array(1) {
	  ["field"]=>
  		string(10) "a@test.com"
	}
*/
```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
