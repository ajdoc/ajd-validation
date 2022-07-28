# Macros
- macros are similar with Reusable rules definition refer to [Usage](../usage.md) jump to **Reuse rule definition**.
- macros may have other use but for now it is currently use like reusable rule definition but some may people create some creative things using macros.
- Set macro first by using `$v->setMacro(string $uniqueMacroName, \Closure $func)`
- Then use the macro by using `$v->macro(string $uniqueMacroName)->check(string $field, mixed $value);`

```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;

$v->setMacro('test_macro', function( AJD_validation $ajd )
{
	$ajd
		->required()
		->minlength(2)
		->maxlength(30);
});

$v->macro('test_macro')->check('macro', '');

/*
	Outputs error
	All of the required rules must pass for "Macro".
	  - The Macro field is required
	  - Macro must be greater than or equal to 4. character(s). 
*/

/*
	It is alos possible to append rules after macro use.
*/
$v->macro('test_macro')->digit()->alpha()->check('macro', '');

/*
	Outputs error
	All of the required rules must pass for "Macro".
	  - The Macro field is required
	  - Macro must be greater than or equal to 4. character(s). 
	  - Macro must contain only digits (0-9).
	  - Macro must contain only letters (a-z).
*/

```

## Differences from store constraint and macro.

- `$v->storeConstraintTo(string $group)->endstoreConstraintTo(); $v->useContraintStorage(string $string)->check($field, mixed $value)`

- `$v->setMacro(string $uniqueMacroName, \Closure $func); $v->macro(string $uniqueMacroName)->check(string $field, mixed $value);`

1. Main difference is setting macro uses a closure/callback setting Constraint storage does not, which opens unique possiblity to macros.