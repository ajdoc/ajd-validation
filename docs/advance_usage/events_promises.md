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
	
	Will trigger once the validation rule has started.

		1. [uniq-event-name] for first paramater
		2. [Closure] for second paramater will received 
			- $event - event name.
			- $closure - current closure (just ignore).
			- $ajd - current ajd validation instance.
			- $value - current given value. 
			- $field - current field.

* publishFail
	
	Will trigger once the validation rule has failed.

		1. [uniq-event-name] for first paramater
		2. [Closure] for second paramater will received 
			- $event - event name.
			- $closure - current closure (just ignore).
			- $ajd - current ajd validation instance.
			- $value - current given value.
			- $field - current field.

* publishSuccess

	Will trigger once the validation rule has passed.

		1. [uniq-event-name] for first paramater
		2. [Closure] for second paramater will received 
			- $event - event name.
			- $closure - current closure (just ignore).
			- $ajd - current ajd validation instance.
			- $value - current given value.
			- $field - current field.

## Promises

Heavily inspired by [reactphp/promise](https://github.com/reactphp/promise).

```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;


	try 
	{
		

		$v
			->required()
				
			->minlength(5)
				
			->check('firstname', 'value-of-firstname')
				->then(
					function()
					{
						$e = 'validation passed or there are no errors that occured while processing this validation.';

						return $e;
					},
					function($exception)
					{
						echo $exception->getMessage();

						$e = 'validation fails or there are error/errors that occured while processing this validation.';

						return $e;
					}
				)
				->then(
					function($e)
					{
						// if validation passes will trigger this
						$e = $e . ' then passed 2 ';

						echo $e // Outputs 
						// validation passed or there are no errors that occured while processing this validation then passed 2
					},
					function($e)
					{
						// if validation fails will trigger this
						$e = $e . ' then fails 2 ';

						echo $e // Outputs 
						// validation passed or there are no errors that occured while processing this validation then fails 2

						throw new Exception($e);
					}
				)
				->otherwise(function(\Exception $e)
				{
					// will receive  exception 
					echo $e->getMessage();

					// Outputs 
						// validation passed or there are no errors that occured while processing this validation then fails 2
				});

		$v
			->required()
				
			->minlength(5)
				
			->check('lastname', 'value-of-lastname')
				->catch(function(Exception $exception)
				{
					// will remove this field-rule error message in the error message bag and will be received in this closure as an exception

					echo $e->getMessage();
				});


		$v->assert();
	}
	catch(Exception $e)
	{
		echo $e->getMessage();
	}

```

This works similarly with reactphp's promise resolution forwarding and Rejection forwarding.

* then(callable $resolution, callable $rejection = null)
	
	1. [callable $resolution] for first paramater 
		- required
		- will trigger if validation passess and is completed

	2. [callable $rejection] for second paramater
		- nullable
		- will trigger if validation fails or is not completed
		

* otherwise(callable $rejection)
	
	1. [callable $rejection] for first paramater 
		- required
		- will trigger if validation fails or is not completed
		- will received an exception

* catch(callable $callback)
	
	1. [callable $callback] for first paramater 
		- required
		- will trigger if validation fails or is not completed
		- will received an exception
		- will remove the current validation field-error message in error message bag/stack.


## Difference vs Events and Promise
```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;


	try 
	{
		
		/*
			example 1
		*/
		$v
			->required()
				
			->minlength(5)
				
			->check('firstname', 'value-of-firstname')
				->fails(function()
				{
					echo 'this fails'
				})
				->fails(function()
				{
					echo 'this fails 2';
				})
				->passed(function()
				{
					echo 'this passed';
				})
				->passed(function()
				{
					echo 'this passed2';
				})
				->then(
					function()
					{
						$e = 'validation passed or there are no errors that occured while processing this validation.';

						return $e;
					},
					function($exception)
					{
						echo $exception->getMessage();

						$e = 'validation fails or there are error/errors that occured while processing this validation.';

						return $e;
					}
				)
				->then(
					function($e)
					{
						// if validation passes will trigger this
						$e = $e . ' then passed 2 ';

						echo $e // Outputs 
						// validation passed or there are no errors that occured while processing this validation then passed 2
					},
					function($e)
					{
						// if validation fails will trigger this
						$e = $e . ' then fails 2 ';

						echo $e // Outputs 
						// validation passed or there are no errors that occured while processing this validation then fails 2

						throw new Exception($e);
					}
				)
				->otherwise(function(\Exception $e)
				{
					// will receive  exception 
					echo $e->getMessage();

					// Outputs 
						// validation passed or there are no errors that occured while processing this validation then fails 2
				});

		/*
			example 2
			This example will cause error
		*/
		$v
			->required()
				
			->minlength(5)
				
			->check('lastname', 'value-of-lastname')
				->catch(function(Exception $exception)
				{
					// will remove this field-rule error message in the error message bag and will be received in this closure as an exception

					echo $e->getMessage();
				})
				->fails(function()
				{
					echo 'this fails will cause error must define first or before promise then,otherwise,catch.';
				});


		$v->assert();
	}
	catch(Exception $e)
	{
		echo $e->getMessage();
	}

```

1. Events does not have resolution/rejection forwarding. It is possible to define multiple and same event listener like in example 1. It is also possible to define multiple and same event listener in rules events.
2. Events must be define first or define before promise's then,otherwise,catch.
3. Promise's catch,otherwise,then rejection can catch not only failed validation but also internal errors while events only triggers if validation fails or passess.
4. Promise has resolution/rejection forwarding and works similarly with [reactphp/promise](https://github.com/reactphp/promise).
5. Promise can be used in this library Async::when feature.
	
See also:
- [Async](advance_usage/async.md)