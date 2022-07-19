## Events

### Validation Events
```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;


	try 
	{
		

		$v
			->required()
			->minlength(5)
			->check('firstname', 'value-of-firstname')
				->fails(function($ajd)
				{
					$ajd 
						->required()
						->check('fails_trigger', '');

					echo 'fails';
				})
				->passed(function($ajd)
				{
					$ajd 
						->required()
						->check('passes_trigger', '');

					echo 'passed';
				});

		$v->assert();
	}
	catch(Exception $e)
	{
		echo $e->getMessage();
	}

```

After defining a field-rule validation you can define to listeners fails or passed or both, both of which must have a closure as their argument and the closure will receive AJD_validation instance as their argument. fails event listener will trigger when validation fails and passed will trigger if the validation passes. You can also create like in the example if this validation fails or passes do another validation. 

### Rules Events
```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;


	try 
	{
		

		$v
			->required()
				->publish('firstname_load_required', function($event, $closure, $ajd, $value = null, $field = null)
				{
					echo 'this will be trigger once validation for this rule has started.  required field:'.$field;
				})
				->publishFail('firstname_fail_required', function($event, $closure, $ajd, $value = null, $field = null)
				{
					echo 'failed required field:'.$field;
				})
			->minlength(5)
				->publishFail('firstname_fail_minlength', function($event, $closure, $ajd, $value = null, $field = null)
				{
					echo 'failed minlength field:'.$field;
				})
				->publishSuccess('firstname_success_minlength', function($event, $closure, $ajd, $value = null, $field = null)
				{
					echo 'success/passed minlength field:'.$field;
				})
			->check('firstname', 'value-of-firstname');

		$v->assert();
	}
	catch(Exception $e)
	{
		echo $e->getMessage();
	}

```
It is also possible to define events per rule definition there are currently three event listener available.

* publish

⋅⋅Will trigger once the validation rule has started.
..test
⋅⋅⋅1. [uniq-event-name] for first paramater
⋅⋅⋅2. [Closure] for second paramater will received 
⋅⋅⋅⋅⋅⋅* $event - event name.
⋅⋅⋅⋅⋅⋅* $closure - current closure (just ignore).
⋅⋅⋅⋅⋅⋅* $ajd - current ajd validation instance.
⋅⋅⋅⋅⋅⋅* $value - current given value.
⋅⋅⋅⋅⋅⋅* $field - current field.

* publishFail
⋅⋅Will trigger once the validation rule has failed.
⋅⋅⋅1. [uniq-event-name] for first paramater
⋅⋅⋅2. [Closure] for second paramater will received 
⋅⋅⋅⋅⋅⋅* $event - event name.
⋅⋅⋅⋅⋅⋅* $closure - current closure (just ignore).
⋅⋅⋅⋅⋅⋅* $ajd - current ajd validation instance.
⋅⋅⋅⋅⋅⋅* $value - current given value.
⋅⋅⋅⋅⋅⋅* $field - current field.

* publishSuccess
⋅⋅Will trigger once the validation rule has passed.
⋅⋅⋅1. [uniq-event-name] for first paramater
⋅⋅⋅2. [Closure] for second paramater will received 
⋅⋅⋅⋅⋅⋅* $event - event name.
⋅⋅⋅⋅⋅⋅* $closure - current closure (just ignore).
⋅⋅⋅⋅⋅⋅* $ajd - current ajd validation instance.
⋅⋅⋅⋅⋅⋅* $value - current given value.
⋅⋅⋅⋅⋅⋅* $field - current field.

## Promises