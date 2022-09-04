<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_all;
use AJD_validation\Contracts\Abstract_invokable;
use AJD_validation\Contracts\Abstract_anonymous_rule;
use AJD_validation\Vefja\Vefja;

class All_rule extends Abstract_all
{
	public function run( $value, $satisfier = null, $field = null )
	{
		if( !EMPTY( $this->getRules() ) )
		{
			foreach( $this->getRules() as $rule )
			{
				$args = [];

				if(
					$rule instanceof Abstract_invokable
					||
					$rule instanceof Abstract_anonymous_rule
				)
				{
					$args = static::$ruleArguments[\spl_object_id($rule)] ?? [];

					if( !$rule( $value, $args, $field ) )
					{
						return false;
					}
				}
				else
				{
					if( !$rule->run( $value, null, $field ) )
					{
						return false;
					}
				}
			}
		}

		return true;
	}

	public function validate( $value )
	{
		if( !EMPTY( $this->getRules() ) )
		{
			foreach( $this->getRules() as $rule )
			{
				$args = [];

				if(
					$rule instanceof Abstract_invokable
					||
					$rule instanceof Abstract_anonymous_rule
				)
				{
					$args = static::$ruleArguments[\spl_object_id($rule)] ?? [];

					if( !$rule( $value, $args ) )
					{
						return false;
					}
				}
				else
				{
					if( !$rule->validate( $value ) )
					{
						return false;
					}
				}
			}
		}

		return true;
	}

	public function assertErr( $value, $override = false, $inverseCheck = null )
	{
		$exceptions = $this->assertRules( $value, $override, $inverseCheck );
		$numRules = count( $this->rules );
		$numExceptions = count( $exceptions );
		$summary = [
			'total' => $numRules,
			'failed' => $numExceptions,
			'passed' => $numRules - $numExceptions
		];
		
		if( !EMPTY( $exceptions ) )
		{
			throw $this->getExceptionError( $value, $summary, null, $override )->setRelated( $exceptions );
		}

		return true;
	}
}