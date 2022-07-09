<?php namespace AJD_validation\Rules;

use AJD_Validation\Contracts\Abstract_rule;
use AJD_validation\Contracts\Uncompromised_interface;

class Uncompromised_rule extends Abstract_rule
{
	public $type = [
		'pwned' => \AJD_validation\Uncompromised\NotPawnedVerifier::class
	];

	public $checkType;
	public $threshold;
	public $args 	= [];

	public $useType = NULL;

	public function __construct($threshold = 0, $checkType = 'pwned')
	{
		$this->checkType = $checkType;
		$this->threshold = $threshold;

		$this->args[] 	= $this->threshold;

		if( ISSET( $this->type[$this->checkType] ) )
		{
			$this->useType = $this->type[$this->checkType];
		}
	}

	public function run( $value, $satisfier = NULL, $field = NULL )
	{
		$check 	= FALSE;

		if( !EMPTY( $this->useType ) )
		{
			if( class_exists($this->useType) )
			{
				$reflection = new \ReflectionClass($this->useType);	
				$valid 		= ( $reflection->isSubclassOf(Uncompromised_interface::class) && !$reflection->isAbstract() );
				
				if( $valid )
				{
					$getConstructor 	= $reflection->getConstructor();

					$resolve 			=  (bool) $getConstructor ? $reflection->newInstanceArgs( $this->args ) : $reflection->newInstanceWithoutConstructor();

					$check 				= $resolve->verify($value);
					
				}
				
			}
			
		}

		return $check;
	}

	public function validate( $value )
	{
		$check 			= $this->run( $value);

		if( is_array( $check ) )
		{
			return $check['check'];
		}

		return $check;
	}
}