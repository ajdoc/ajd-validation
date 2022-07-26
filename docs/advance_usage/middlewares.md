# Middlewares
- middlewares are closures that happens before the validation
```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;

/*
	Creating a middleware
*/

// using middleware for conditional validation
/*
	Here all field-rule validation that uses test_middleware will be checked first if $params['a'] is not empty. If empty all validation will return a FailedPromise Error 'Middleware failed'. If not empty all validation will run.
*/

$params [
	'a' => ''
];

$v->setMiddleWare('test_middleware', function( $ajd, $func, $args ) use ($params)
{
	$ajd2 	= $ajd->getValidator();

	$ch  = $ajd2->required()->validate($params['a']);
	
	if( $ch )
	{
		return $func($ajd, $args);
	}
});

// Apply middleware to a certain field-rule definition
$v->required()
	->minlength(2)
	->middleware('test_middleware','field1', '');

/*
	Since $params['a'] is empty this validation will not run and this definition will return a FailedPromise 'Middleware Failed.'. If $params['a'] is not empty validation will run but since 'field1' is empty it will return a FailedPromise 'Validation Failed' and will output error
		
		If $params['a'] has value.
			All of the required rules must pass for "Field1".
		  		- The Field1 field is required
		  		- Field1 must be greater than or equal to 2. character(s). 
*/

```

## Running All defined middleware
- If you have defined multiple middleware you can apply all defined middleware. But do note that if just one of the defined middleware failed then validation would not run and `->checkAllMiddleware(string $field1, mixed $value);` will return a FailedPromise 'Middleware Failed'.

```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;

/*
	Creating multiple middleware
*/

// using middleware for conditional validation

$params [
	'a' => 'a'
];

$v->setMiddleWare('test_middleware', function( $ajd, $func, $args ) use ($params)
{
	$ajd2 	= $ajd->getValidator();

	$ch  = $ajd2->required()->validate($params['a']);
	
	if( $ch )
	{
		return $func($ajd, $args);
	}
});

$v->setMiddleWare('test_middleware2', function( $ajd, $func, $args )
{
	echo 'middleware 2';
	
	return $func($ajd, $args);
	
});

// Apply all middleware to a certain field-rule definition.
/*
	- Since no middleware failed it will run the validation.
	- Middlewares will run in order of their definition. So in the example test_middleware will run first then test_middleware2

*/
$v->required()
	->minlength(2)
	->checkAllMiddleware('field1', '');

```