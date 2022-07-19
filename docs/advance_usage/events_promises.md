## Events
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

## Promises