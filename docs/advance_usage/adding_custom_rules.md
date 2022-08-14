## Adding Custom Rules

### Adding Rules Directory and Rules Namespace
- You can add custom rules by adding a new rules directory and add rules namespace
- Rules under the new directory must extend to \AJD_validation\Contracts\Abstract_rule
- When adding a new Rule using a new Rules Directory one must add Rule exception for that rule inside new Rules Directory\Exceptions folder.
- All new custom rule must have [Custom]`_rule` as a suffix and all new custom exception must have [Custome]`_rule_exception` as a suffix

```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;

$v
	->addRuleDirectory(__DIR__.DIRECTORY_SEPARATOR.'CustomRules'.DIRECTORY_SEPARATOR)
	->addRuleNamespace('CustomRules\\');

/*
	Inside Custom Rules directory one must create exceptions directory also
	CustomRules\
		Exceptions\
			Custom_rule_exception.php
		Custom_rule.php
*/

/*
	Custom_rule class
*/
namespace CustomRules;

use AJD_validation\Contracts\Abstract_rule;

class Custom_rule extends Abstract_rule
{

	public function run( $value, $satisfier = NULL, $field = NULL, $clean_field = NULL, $origValues = NULL )
	{
		return strtolower($value) == 'custom_rule';
	}

	public function validate( $value )
	{
		$check 	= false;
		$check 	= $this->run( $value );

		if( is_array( $check ) )
		{
			return $check['check'];
		}

		return $check;
	}

}

/*
	Custom_rule_exception class
*/

namespace CustomRules\Exceptions;

use AJD_validation\Contracts\Abstract_exceptions;
use AJD_validation\Constants\Lang;

class Custom_rule_exception extends Abstract_exceptions
{
    public static $defaultMessages  = [
        self::ERR_DEFAULT           => [
            self::STANDARD          => 'The :field is validated using custom rule.',
        ],
        self::ERR_NEGATIVE          => [
         self::STANDARD             => 'The :field is not validated using custom rule.',
        ],
    ];

    /*
    	if with localization error message
    */
    public static $localizeMessage 	= [
		Lang::FIL => [
			self::ERR_DEFAULT 			=> [
			 	self::STANDARD 			=> 'The :field field ay ginagamit ang custom rule',
			 ],
			  self::ERR_NEGATIVE 		=> [
	            self::STANDARD 			=> 'The :field field ay hindi ginagamit ang custom rule.',
	        ],
		]
	];

    public static $localizeFile     = 'custom_rule_err';
}

/*
	
	- After defining both the rule and rule exception class you can use now your custom validation
	- When using your custom rule just remove `_rule` suffix
*/

$v 
	->custom()
	->check('custom_field', ''); // outputs error 
/*
	Outputs error
	The Custom Field is validated using custom rule
*/

$v 
	->getValidator()
	->custom()
	->validate(''); // false

$v 
	->custom()
	->check('custom_field', 'custom_rule'); // validation passes

$v 
	->getValidator()
	->custom()
	->validate('custom_rule'); // true
```

#### Adding an invokable rule
	- For simple rule implementation create invokable rule
	- You can add custom invokable rules under the new rules directory and rules namespace by extending to \AJD_validation\Contracts\Abstract_invokable

```php

namespace CustomRules;

use AJD_validation\Contracts\Abstract_invokable;
use AJD_validation\Constants\Lang;

class Invokable_custom_rule extends Abstract_invokable
{
    public function __construct()
    {
    }

	public function __invoke($value, $satisfier = NULL, $field = NULL)
    {
        $check = strtolower($value) == 'invokable_custom_rule';

        if($this->exception)
        {

            return $this->exception->message($check, [
                $this->exception::ERR_DEFAULT => [
                    $this->exception::STANDARD => 'The :field field is validated using custom invokable.'
                ],
                $this->exception::ERR_NEGATIVE      => [
                    $this->exception::STANDARD          => 'The :field field is not custom invokable.',
                ],
                /*
                	If you want localization
                */
                Lang::FIL => [
                    $this->exception::ERR_DEFAULT => [
                        $this->exception::STANDARD => 'The :field* field ay custom invokable.'
                    ],
                    $this->exception::ERR_NEGATIVE      => [
                        $this->exception::STANDARD          => 'The :field* field ay hindi custom invokable.',
                    ]
                ]
            ]);
            
        }

        return $check;


    }
}

use AJD_validation\AJD_validation;
$v = new AJD_validation;

$v 
	->invokable_custom()
	->check('invokable_custom_field', ''); // outputs error 
/*
	Outputs error
	The Invokable Custom Field field is validated using custom invokable.
*/

$v 
	->getValidator()
	->invokable_custom()
	->validate(''); // false

$v 
	->invokable_custom()
	->check('invokable_custom_field', 'invokable_custom_rule'); // validation passes

$v 
	->getValidator()
	->custom()
	->validate('invokable_custom_rule'); // true
```

#### Registering Rules Mapping 
	- If your app is already using some sort of autoloading you can use this to register rules and exceptions key value pair.
	- Use this if you dont want to register the whole rules directory and just want to register specific rules/exceptions.

```php
use AJD_validation\AJD_validation;
$v = new AJD_validation;

$v ->registerRulesMappings([
	\CustomNamespace\Rules\Custom_rule::class => \CustomNamespace\Exceptions\Custom_rule_exception::class
]);

$v->custom()->check('custom', '');
$v->getValidator()->custom()->validate('');

```

## Registering custom rules using Anonymous class

```php
use AJD_validation\AJD_validation;
use AJD_validation\Contracts\Abstract_anonymous_rule;
use AJD_validation\Contracts\Abstract_exceptions;
use AJD_validation\Constants\Lang;

$v = new AJD_validation;

$v->registerAnonClass(
	new class() extends Abstract_anonymous_rule
	{
		/*
			must return boolean
		*/
		public function __invoke($value, $satisfier = NULL, $field = NULL)
		{
			return in_array($value, $satisfier);

		}

		/*
			your custom rule name
		*/
		public static function getAnonName() : string
		{
			return 'custom_anonymous';
		}

		public static function getAnonExceptionMessage(Abstract_exceptions $exceptionObj)
		{
			$exceptionObj::$defaultMessages 	= [
				 $exceptionObj::ERR_DEFAULT 			=> [
				 	$exceptionObj::STANDARD 			=> 'The :field field is validated using custom_anonymous rule.',
				 ],
				  $exceptionObj::ERR_NEGATIVE 		=> [
		            $exceptionObj::STANDARD 			=> 'The :field field is not validated using custom_anonymous rule.',
		        ]
			];

			$exceptionObj::$localizeMessage 	= [
				Lang::FIL => [
					$exceptionObj::ERR_DEFAULT 			=> [
					 	$exceptionObj::STANDARD 			=> 'The :field field localization example for custom_anonymous rule.',
					 ],
					  $exceptionObj::ERR_NEGATIVE 		=> [
			            $exceptionObj::STANDARD 			=> 'The :field field not localization example for custom_anonymous rule.',
			        ],
				]
			];
		}
	}
);

$v 
	->custom_anonymous(3)
	->check('anonymous_custom_field', '1'); // outputs error 
/*
	Outputs error
	The Anonymous Custom Field field is validated using custom_anonymous rule.
*/


$v 
	->custom_anonymous(3)
	->check('anonymous_custom_field', '3'); // validation passes


```
	- **Note: Custom rules Registered using anoymous class won't work when using `$v->getValidator()->custom_anonymous()`, might add support in the future.**

## Registering Custom Rule using `$v->registerClass()`
```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;

class Custom_class
{
	// must have a method run
	public function run( $value = null, $satisfier = null, $field = null )
	{
		if( $value == 'a' )
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}

/*
	1. Register the rule name and the Object, object must have method run
	2. Then add rule msg for rule name.
*/

$v->registerClass('custom_class', new Custom_class);
$v->add_rule_msg('custom_class', 'this value is not custom class a');

$v->custom_class()->check('custom_class', ''); // outputs error 
/*
	Outputs error
		this value is not custom class a
*/

$v->custom_class()->check('custom_class', 'a'); // validation passes
```
	- **Note: Custom rules Registered using `$v->registerClass()` won't work when using `$v->getValidator()->custom_class()`. There is also no localization support for this way of adding custom rule.**

## Registering Custom Rule using `$v->registerMethod()`
```php
use AJD_validation\AJD_validation;

$v = new AJD_validation;

class Custom_method
{
	// there must be a suffix _rule to the method name to avoid method conflict.
	// whatever name you register as method name in `$v->registerMethod()` there must be always a suffix _rule
	public function custom_method_rule( $value = null, $satisfier = null, $field = null )
	{
		if( $value == 'a' )
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}

/*
	1. Register the rule name and the Object, object must have method name with a suffix _rule
		e.g. 'custom_method_rule'

	2. Then add rule msg for rule name.
*/
$v->registerMethod('custom_method', new Custom_method);
$v->add_rule_msg('custom_method', 'this value custom method is not a');

$v->custom_method()->check('custom_method', ''); // outputs error 
/*
	Outputs error
		this value custom method is not a
*/

$v->custom_method()->check('custom_method', 'a'); // validation passes
```
	- **Note: Custom rules Registered using `$v->registerMethod()` won't work when using `$v->getValidator()->custom_method()`. There is also no localization support for this way of adding custom rule.**

## Registering custom rule using `$v->registerFunction()`
```php
use AJD_validation\AJD_validation;
$v = new AJD_validation;
// custom function using callback/Closure
/*
	1. Register the rule name and the callback/Closure
	2. Then add rule msg for rule name.
*/
$v->registerFunction('my_custom_func', function($value, $field, $satisfier)
{
	if( $value == 'a' )
	{
		return true;
	}
	else
	{
		return false;
	}
});

$v->add_rule_msg('my_custom_func', 'this value is not a');

$v->my_custom_func()->check('my_custom_func', 'b'); // outputs error
/*
	Outputs error
		this value is not a
*/

$v->my_custom_func()->check('my_custom_func', 'a'); // validation passes
```
	- **Note: Custom rules Registered using `$v->registerFunction()` won't work when using `$v->getValidator()->my_custom_func()`. There is also no localization support for this way of adding custom rule.**

### Default php function supported
	- error message for the following supported php function is found under src/AJD_validation/lang/[current_lang]_lang.php
```php
	[
		'filter_var',
		'in_array',
		'preg_match',
		'is_int',
		'is_numeric',
		'is_array',
		'is_float',
		'is_string',
		'is_object',
		'is_callable',
		'is_bool',
		'is_null',
		'is_resource',
		'is_scalar',
		'is_finite'
	]

$v->is_numeric()->check('is_numeric_field', 'a'); // outputs error
/*
	Outputs error
		Is numeric field must be numeric.
*/
$v->is_numeric()->check('is_numeric_field', '1'); // validation passes

/*
	Third paramater false turns off auto loop array checking
*/
$v->is_array()->check('is_array_field', '', false); // outputs error
/*
	Outputs error
		Is array field must be a php array.
*/
$v->is_array()->check('is_array_field', [], false); // validation passes		
```
	- **Note: these default php functions won't work when using `$v->getValidator()->is_array()`.**

## Registering custom rule using `$v->registerExtension()`
	- registering extension not only registers custom rule but can also register custom filtering, custom logics, custom middlewares, custom anonymous class rule.
```php
use AJD_validation\Contracts\Base_extension;
use AJD_validation\AJD_validation;
use AJD_validation\Contracts\Abstract_exceptions;
use AJD_validation\Contracts\Abstract_anonymous_rule;

$v = new AJD_validation;

class Custom_extension extends Base_extension
{
	/*
		Desired name of the extension
	*/
	public function getName()
	{
		return 'Custom_extension';
	}

	/*
		Adding custom rule method
	*/
	public function getRules()
	{
		return [
			'custom_validation_rule',
			'custom_validation2_rule'
		];
	}

	/*
		Adding custom rule method error message
		When adding custom rule method error message remove _rule suffix for the key
	*/
	public function getRuleMessages()
	{
		return [
			'custom_validation' 	=> 'The :field field is not a a.',
			'custom_validation2' 	=> 'The :field field is not a a 2.',
		];
	}

	/*
		rule method must always have _rule suffix
	*/
	public function custom_validation_rule( $value, $satisfier, $field )
	{
		if( $value == 'a' )
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/*
		rule method must always have _rule suffix
	*/
	public function custom_validation2_rule( $value, $satisfier, $field )
	{
		return false;
	}

	/*
		Adding custom rule using anonymous class
	*/
	public function getAnonClass()
	{
		return [
			new class() extends Abstract_anonymous_rule
			{
				public function __invoke($value, $satisfier = NULL, $field = NULL)
				{
					return in_array($value, $satisfier);
				}

				public static function getAnonName() : string
				{
					return 'ext1_anontest';
				}

				public static function getAnonExceptionMessage(Abstract_exceptions $exceptionObj)
				{
					$exceptionObj::$defaultMessages 	= array(
						 $exceptionObj::ERR_DEFAULT 			=> array(
						 	$exceptionObj::STANDARD 			=> 'The :field field is ext1_anontest',
						 ),
						  $exceptionObj::ERR_NEGATIVE 		=> array(
				            $exceptionObj::STANDARD 			=> 'The :field field is not ext1_anontest.',
				        )
					);
				}
			},
			new class() extends Abstract_anonymous_rule
			{
				public function __invoke($value, $satisfier = NULL, $field = NULL)
				{
					return in_array($value, $satisfier);
				}

				public static function getAnonName() : string
				{
					return 'ext2_anontest';
				}

				public static function getAnonExceptionMessage(Abstract_exceptions $exceptionObj)
				{
					$exceptionObj::$defaultMessages 	= array(
						 $exceptionObj::ERR_DEFAULT 			=> array(
						 	$exceptionObj::STANDARD 			=> 'The :field field is ext2_anontest',
						 ),
						  $exceptionObj::ERR_NEGATIVE 		=> array(
				            $exceptionObj::STANDARD 			=> 'The :field field is not ext2_anontest.',
				        )
					);

					$exceptionObj::$localizeMessage 	= [
						Lang::FIL => [
							$exceptionObj::ERR_DEFAULT 			=> array(
							 	$exceptionObj::STANDARD 			=> 'The :field field ay ext2_anontest',
							 ),
							  $exceptionObj::ERR_NEGATIVE 		=> array(
					            $exceptionObj::STANDARD 			=> 'The :field field ay hindi ext2_anontest.',
					        ),
						]
					];
				}
			}
		];
	}

	/*
		Adding custom filters
	*/
	public function getFilters()
	{
		return [
			'custom_string_filter',
		];
	}

	/*
		filter method must always have _filter suffix
	*/
	public function custom_string_filter( $value, $satisfier, $field )
	{
		$value 	= filter_var( $value, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES ).'_from_extension';

		return $value;
	}

	/*
		Adding custom middlewares
	*/
	public function getMiddleWares()
	{
		return [];
	}

	/*
		Adding custom logics
	*/
	public function getLogics()
	{
		return [
			'custom_logics_logic'
		];
	}

	/*
		logics method must alwasy have _logic suffix
	*/
	public function custom_logics_logic($value = null, ...$satisfier) : bool
	{
		return $value == $satisfier[0];
	}
}

/*
	1. Register the extension object.
*/
$extension 	= new Custom_extension;
$v->registerExtension($extension);

$v->custom_validation()->custom_validation2()->check('custom_extension', '');
/*
	Outputs error
		All of the required rules must pass for "Custom extension".
		  - The Custom extension field is not a a.
		  - The Custom extension field is not a a 2.
*/

$v->ext1_anontest(3)->check('ext1_anontest', '1');
/*
	Outputs error
		The Ext1 anontest field is ext1_anontest
*/

$v->ext2_anontest(3)->check('ext2_anontest', '1');
/*
	Outputs error
		The Ext2 anontest field is ext2_anontest
*/

/*
	using custom logic
*/
$v->Lgcustom_logics(5)->runLogics('1'); // false
$v->Lgcustom_logics(5)->runLogics('5'); // true
```
	- **Note: custom rules registered using `$v->registerExtension()` won't work when using `$v->getValidator()->custom_validation()`. Might add support for custom rules using Anonymous class in `$v->getAnonClass()` to work when using `$v->getValidator()->ext1_anontest()` in the future.**