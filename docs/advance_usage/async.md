## Async 

**Not really asynchronous**

### Async when

Not really asynchronous just emulates jquery's $.when function

```php
use AJD_validation\AJD_validation as v;
use AJD_validation\Async\Async;

$v = new v;


	try 
	{
		
		Async::when(
			$v
				->required()
				->minlength(5)
				->check('firstname', 'value-of-firstname'),
				
			$v
				->required()
				->minlength(5)
				->check('lastname', 'value-of-lastname')
		)
		->promise()
		->then(function()
		{
			echo 'all of the defined validation passes';

			v::required()
				->check('then_passes');
			
		}, function()
		{
			echo 'some or all of the defined validation fails';

			v::required()
				->check('then_fails');
		})


		$v->assert();
	}
	catch(Exception $e)
	{
		echo $e->getMessage();
	}

```
Async when emulates jquery's $.when function define all the field-rule validation needed and if all passess promise then(callabale $resolution) will be triggered if one or all fails then(callabale $rejection) will be triggered and error message will be catchable and can be access thru catch(callable $callback). See events_promises.md for promise documents.

See also:
- [Async](events_promises.md)