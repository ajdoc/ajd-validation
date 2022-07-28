# Date

- `Fdate(string $dateFormat = 'Y-m-d')`
- `Date_filter(string $dateFormat = 'Y-m-d')`

Converts a string date to valid date format.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->Fdate('Y-m-d', true)->check('field', '01/01/2022');
$v->Fdate('m/d/Y')->check('field', '2020-01-01');

$pre_filter = $v->pre_filter_value();
/*
	returns array;
	array(1) {
	  ["field"]=>
  		string(10) "2022-01-01"
	  
	}
*/
$filtered_values = $v->filter_value();

/*
	returns array;
	array(1) {
	  ["field"]=>
  		string(10) "01/01/2020"
	}
*/
```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
