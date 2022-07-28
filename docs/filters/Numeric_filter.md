# Numeric filter

- `Fnumeric()`
- `Numeric_filter()`

Uses php's `filter_var($value, FILTER_SANITIZE_NUMBER_INT )` function

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->Fnumeric(null, true)->check('field', 's');
$v->Fnumeric()->check('field', '1aa');

$pre_filter = $v->pre_filter_value();
/*
	returns array;
	array(1) {
	  ["field"]=>
  		string(0) ""
	}
*/
$filtered_values = $v->filter_value();

/*
	returns array;
	array(1) {
   		["field"]=>
 		string(1) "1"
	}
*/
```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
