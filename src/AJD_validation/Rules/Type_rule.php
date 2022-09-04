<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_rule;

class Type_rule extends Abstract_rule
{
	public $type;
	public $validType = [
		'array' => 'array',
		'bool' => 'boolean',
		'boolean' => 'boolean',
		'callable' => 'callable',
		'double' => 'double',
		'float' => 'double',
		'int' => 'integer',
		'integer' => 'integer',
		'null' => 'NULL',
		'object' => 'object',
		'resource' => 'resource',
		'string' => 'string'
	];

	protected $callableName = ['callable'];

	public function __construct( $type )
	{
		$lowerType = strtolower( $type );

		if( !ISSET( $this->validType[ $type ] ) )
		{
			throw new Exception(
				sprintf('"%s" is not a valid type', print_r($type, true))
			);
		}

		$this->type = $type;
	}

	public function run( $value, $satisfier = null, $field = null )
	{
		$check = false;
		$lowerType = strtolower( $this->type );

		if( in_array( $lowerType, $this->callableName ) )
		{
			$check = is_callable( $value );
		}
		else
		{
			$check = ( $this->validType[$lowerType] === gettype( $value ) );
		}

		return $check;
	}

	public function validate( $value )
	{
		$check = $this->run( $value );

        if( is_array( $check ) )
        {
            return $check['check'];
        }

        return $check;
	}
}