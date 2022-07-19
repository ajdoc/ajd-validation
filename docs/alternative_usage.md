# Another Way of doing Things

In this document we'll see how to use an alternative way of defining fields-rules.

## Alternative usage
```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;

	try 
	{
		$v

		->Srequired()
			->field('username')
				->minlength(2)
				->alpha()
			->field('fname')
				->minlength(1)
		->eSrequired()

		->Sdigit()
			->field('digit_group')
			->field('digit_group2')
		->eSdigit()

		->checkGroup([
			'username' => 'aa',
			'fname' => '',
			'digit_group' => '1',
			'digit_group2' => ''
		]);

		$v->assert();
	}
	catch(Exception $e)
	{
		echo $e->getMessage();
	}

```

Here we define rules differently we use the prefix `S` then followed by the rulename `->Srequired()` to signify that we will define fields under that rule.

To define fields we chain `->field('username')` inside is the field name. Then if we want to say that the minlength for that field is 2, then we just use the normal way of defining rules `->minlength(2)` and we can also add the alpha `->alpha()` rule.

To add another field just `->field('fname')` again with the different field and under that field you can define its own set of rule as shown by the example.

Then to finish the rule grouping we add prefix `eS` then followed by the rulename `->eSrequired()` and we can add another rule grouping as shown by the example.

And to finished use `->checkGroup()` containing the associative array as shown by the example. **This `->checkGroup()` will return a promise please see**
	- [Events and Promises](advance_usage/events_promises.md) for documentation

The above definition will output an error

```
All of the required rules must pass for "Username".
  - The Username field is required
  - Username must be greater than or equal to 2. character(s). 
  - Username must contain only letters (a-z).
All of the required rules must pass for "Fname".
  - The Fname field is required
  - Fname must be greater than or equal to 1. character(s). 
All of the required rules must pass for "Digit group2".
  - Digit group2 must contain only digits (0-9).
```

## Using or logic in rule grouping

```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;

	try 
	{
		$v

		->Srequired(null, AJD_validation::LOG_OR)
			->field('username')
				->minlength(2)
				->alpha()
			->field('fname')
				->minlength(1)
		->eSrequired()

		->Sdigit()
			->field('digit_group')
			->field('digit_group2')
		->eSdigit()

		->checkGroup([
			'username' => 'aa',
			'fname' => '',
			'digit_group' => '1',
			'digit_group2' => ''
		]);

		$v->assert();
	}
	catch(Exception $e)
	{
		echo $e->getMessage();
	}

```

This will output an error of 

```
All of the required rules must pass for "Digit group2".
  - Digit group2 must contain only digits (0-9).
```

It will no longer output fname since in the grouping logic we stated if username or fname passes required.

## We can also use field event and rules events,promises and scenarios

```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;

	try 
	{
		$v
		->Srequired(NULL, AJD_validation::LOG_AND)
			->field('username')->on('edit')
				->publishFail('supper_test', function()
				{
					echo '<pre>';
					echo 'super field test required only.';
				})
				->minlength(2)->on('edit')
				->alpha()
			->field('fname')
				->publishFail('supper_minelen_test', function()
				{
					echo '<pre>';
					echo 'super field test minlength.';
				})
				->minlength(1)
					->publishFail('minelen_test', function()
					{
						echo '<pre>';
						echo 'minlength test.';
					})
					->publishFail('minlengthtest2', function()
					{
						echo '<pre>';
						echo 'super field test minlength 2.';
					})
		->eSrequired()
		->Sdigit(NULL, AJD_validation::LOG_AND)
			->field('digit_group')
			->field('digit_group2')
		->eSdigit()
		->checkGroup([
			'username' => '',
			'fname' => 'a',
			'digit_group' => '1',
			'digit_group2' => ''
		])
		->then(function()
		{
			echo 'group passed';
		}, function()
		{
			echo 'group failed';
		});
	}
	catch(Exception $e)
	{
		echo $e->getMessage();
	}
```

See also:

- [Events and Promises](advance_usage/events_promises.md)
- [Scenarios](advance_usage/scenarios.md)
- [Usage](usage.md)