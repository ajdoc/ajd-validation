## Adding Custom Rules

### Adding Rules Directory and Rules Namespace
- You can add custom rules by adding a new rules directory and add rules namespace
- Rules under the new directory must extend to \AJD_validation\Contracts\Abstract_rule
- When adding a new Rule using a new Rules Directory one must add Rule exception for that rule inside new Rules Directory\Exceptions folder.
- All new custom rule must have [Custom]`_rule` as a suffix and all new custom exception must have [Custome]`_rule_exception` as a suffix

```php
use AJD_validation\AJD_validation;

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
	The custom_field is validated using custom rule
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
	- You can add custom invokable rules under the new rules directory and rules namespace by extending to \AJD_validation\Contracts\Abstract_invokable

```php
use AJD_validation\Contracts\Abstract_invokable;
use AJD_validation\Constants\Lang;

class Invokable_custom_rule extends Abstract_invokable
{
    public function __construct()
    {
    }

	public function __invoke($value, $satisfier = NULL, $field = NULL)
    {
        $check = strtolower($value) == 'custom_rule';

        if($this->exception)
        {

            return $this->exception->message($check, [
                $this->exception::ERR_DEFAULT => [
                    $this->exception::STANDARD => 'The :field field is custom invokable.'
                ],
                $this->exception::ERR_NEGATIVE      => [
                    $this->exception::STANDARD          => 'The :field field is not custom invokable.',
                ],
                /*
                	If you want localiztion
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
```