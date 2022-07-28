# Std to array filter

- `Fstd_to_array()`
- `Std_to_array_filter()`

Convert value to php array

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->Fstd_to_array(null, true)->check('field', 's');
$objt = new StdClass;
$objt->ff = '';
$objt->f1 = '';
$v->Fstd_to_array()->check('field', $objt, false);

$v->Fstd_to_array()->check('field_1', [1,2], false);


$pre_filter = $v->pre_filter_value();
/*
	returns array;
	array(1) {
	   ["field"]=>
		  array(1) {
		    [0]=>
		    string(1) "s"
		  }
	}
*/
$filtered_values = $v->filter_value();

/*
	returns array;
	array(2) {
   		["field"]=>
		  array(2) {
		    ["ff"]=>
		    string(0) ""
		    ["f1"]=>
		    string(0) ""
		  }
		["field_1"]=>
		  array(2) {
		    [0]=>
		    int(1)
		    [1]=>
		    int(2)
		  }
	}
*/
```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
