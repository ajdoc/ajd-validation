<?php 

use AJD_validation\Contracts\Base_extension;
use AJD_validation\Helpers\Database;

class Core_extensions extends Base_extension
{
	public function getRules()
	{
		return array(
			'required_ext_rule',
			'super_required_rule',
			'func_rule',
			'unique_ext_rule',
			'q_rule'
		);
	}

	public function getRuleMessages()
	{
		return array(
			'required_ext' => 'The :field field is required extension. .',
			'super_required' => 'The :field field is super required.',
			'func' 			=> 'The :field field is a function.',
			'unique_ext' 	=> 'The :field field must be unique in table :0.',
			'q' 			=> 'What'
		);
	}

	public function getMiddleWares()
	{
		return array(
			'extMiddle' => $this->extMiddle()
		);
	}

	protected function extMiddle()
	{
		return function( $v, $next, $args ) {

			$next( $v, $args );
			$v::required()->check('me');
			
		};
	}

	public function runRules( $rule, $value, $satisfier, $field )
	{

		if( method_exists( $this , $rule ) )
		{
			return $this->{ $rule }( $value, $satisfier, $field );
		}
		else 
		{	
			return call_user_func_array( $rule , array( $value, $satisfier, $field ) );
		}
	}

	public function getName()
	{
		return 'Core_extensions';
	}

	protected function required_ext_rule()
	{
		return false;
	}

	protected function super_required_rule()
	{
		return false;
	}

	protected function unique_ext_rule( $value, $satisfier, $field )
	{
		if( ISSET( $satisfier['connection'] ) AND !EMPTY( $satisfier['connection'] ) )
		{
			if( is_object( $satisfier['connection'] ) )
			{
				if( $satisfier['connection'] instanceof AJD_validation\Helpers\Database )
				{
					$db 	= $satisfier['connection'];
				}
				else 
				{
					$db 	= new Database( $satisfier['connection'] );	
				}
				
			}
			else 
			{
				$conn 	= explode( '|', $satisfier['connection'] );

				$db 	= new Database( $conn[0], $conn[1], $conn[2] );

			}

		}
		else 
		{
				$db 	= new Database( 'mysql:host=127.0.0.1;dbname=vawdocs', 'root', 'doctor' );
		}

		$count 	= $db
					->select( $satisfier['select'] )
					->from( $satisfier['table'] )
					->where( $satisfier['where'], $value )
					->rowCount();

		return EMPTY( $count );

	}

	protected function q_rule( $value, $satisfier, $field )
	{
		$db 	= new Database( 'mysql:host=127.0.0.1;dbname=vawdocs', 'root', 'doctor' );

		$db->distinct()->select( 'a.c' )->from( 'param_lgu a' ); 
		if( 1 == 1 )
		{
			$db->where( '1', '2' );
		}
		else 
		{
			$db->where( 'id', 3 );
		}

		print_r( 
					$db->debug()
				);

		return false;

	}

}

function func_rule( $value, $satisfier, $field )
{
	return false;
}