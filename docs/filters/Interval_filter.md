# Interval

- `Finterval()`
- `Interval_filter()`

Filters/Convert data to length if string, if numeric just returns the numeric value and datetime object if date.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->Finterval(null, true)->check('filter_interval_field', 'a');
$v->Finterval()->check('filter_interval_field', '2020-01-01');

$pre_filter = $v->pre_filter_value();
/*
	returns array;
	array(1) {
	  ["filter_interval_field"]=>
  		int(1)
	  
	}
*/
$filtered_values = $v->filter_value();

/*
	returns array;
	array(1) {
	  
	  ["filter_interval_field"]=>
		  object(DateTime)#164 (3) {
		    ["date"]=>
		    string(26) "2020-01-01 00:00:00.000000"
		    ["timezone_type"]=>
		    int(3)
		    ["timezone"]=>
		    string(3) "UTC"
		  }
	}
*/
```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
