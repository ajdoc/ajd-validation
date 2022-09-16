# Cast_to

- `Fcast_to()`
- `Cast_to_filter(string $type)`

Cast the value using php's settype .

```php
use AJD_validation\AJD_validation as v;

$v = new v;

$v->Fcast_to('double', true)->check('field', 'john(.doe)@exa//mple.com');
$v->Fcast_to('double')->check('field', 'a@test.com');

$pre_filter = $v->pre_filter_value();
/*
	returns array;
	array(1) {
	  ["field"]=>
  		float(0)
	  
	}
*/
$filtered_values = $v->filter_value();

/*
	returns array;
	array(1) {
	  ["field"]=>
  		float(0)
	}
*/

/*
	casting to datetime object
*/
$v->Fcast_to('DateTime', true)->check('field', '2022-01-01');
$v->Fcast_to('DateTime')->check('field', '2022-01-01');

$pre_filter = $v->pre_filter_value();
/*
	returns array;
	array(1) {
	  ["field"]=>
  		 object(DateTime)#24 (3) {
		    ["date"]=>
		    string(26) "2022-01-01 00:00:00.000000"
		    ["timezone_type"]=>
		    int(3)
		    ["timezone"]=>
		    string(13) "Europe/Berlin"
		  }
	  
	}
*/
$filtered_values = $v->filter_value();

/*
	returns array;
	array(1) {
	  ["field"]=>
  		 object(DateTime)#24 (3) {
		    ["date"]=>
		    string(26) "2022-01-01 00:00:00.000000"
		    ["timezone_type"]=>
		    int(3)
		    ["timezone"]=>
		    string(13) "Europe/Berlin"
		  }
	}
*/
```

## Changelog

Version | Description
--------|-------------
  0.1.0 | Initial Release

***
