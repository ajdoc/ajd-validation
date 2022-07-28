# Extract length

- `Fextract_length()`
- `Extract_length_filter()`

Extract a value's length dynamically.
* if string extract string length.
* if array or countable extract count.
* if object extract count.
* if numeric extract number length.

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->Fextract_length(null, true)->check('field', [1,2,3], false);
$v->Fextract_length(null, true)->check('field_pre_1', ['field' =>[1, 24]]);
$v->Fextract_length()->check('field', '11');
$v->Fextract_length()->check('field_1', 'john');
$v->Fextract_length()->check('field_2', (object) ['1a', '3s' => '2', 's' => '2'], false);

$objt = new StdClass;
$objt->ff = '';
$objt->f1 = '';
$v->Fextract_length()->check('field_3', $objt, false);

$pre_filter = $v->pre_filter_value();
/*
	returns array;
	array(2) {
	  ["field"]=>
  		int(3)
	  ["field_pre_1"] =>
		array(2) {
		    [0]=>
		    int(1)
		    [1]=>
		    int(2)
		  }
	  
	}
*/
$filtered_values = $v->filter_value();

/*
	returns array;
	array(4) {
   		["field"]=>
 		int(2)
	  	["field_1"]=>
  		int(4)
  		["field_2"]=>
  		int(3)
  		["field_3"]=>
  		int(2)
	}
*/
```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
