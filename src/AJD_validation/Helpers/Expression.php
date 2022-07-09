<?php namespace AJD_validation\Helpers;

use AJD_validation\Factory\Factory_strategy;

class Expression
{
	const EXPR_AND 									= 'AND';
	const EXPR_OR 									= 'OR';
	const EXPR_XOR 									= 'XOR';

	const REGEX_AND 								= 'AND|[\&]';
	const REGEX_OR 									= 'OR|[\|]';
	const REGEX_XOR 								= 'XOR|[\^]';

	protected static $expr;
	protected static $value;

	protected static $ajd;

	protected static $valid_logic 					= array(
		'empty',
		'EMPTY',
		'isset',
		'ISSET',
		'==',
		'equals',
		'object',
		'required',
		'maxlength'		
	);

	protected static $logic_method_name 			= array(
		'empty'			=> 'process_empty',
		'isset' 		=> 'process_isset',
		'==' 			=> 'process_equals',
		'equals' 		=> 'process_equals',
		'object' 		=> 'process_object',
		'required' 		=> 'process_validate',
		'maxlength' 	=> 'process_validate'
	);

	protected static $single_args 					= array(
		'maxlength'
	);

	protected static $valid_inverse_logic_symbol 	= '/!|not|NOT/';

	protected static $self_instance;

	protected static $factory;

	public function __construct( $expr, array $value, $ajd )
	{
		static::$expr 	= $expr;
		static::$value 	= $value;

		static::$ajd 	= $ajd;
	}

	protected static function get_factory_instance()
	{
		if( !static::$factory instanceof Factory_strategy ) 
		{
			static::$factory 	= new Factory_strategy();
		}

		return static::$factory;
	}

	protected static function get_expression_instance()
	{
		if( static::$self_instance === NULL ) 
		{
			static::$self_instance	= new Expression( static::$expr, static::$value, static::$ajd );
		}

		return static::$self_instance;
	}

	public static function evaluate()
	{
		return static::parse_expr();
	}

	public function validate()
	{
		return static::parse_expr();
	}

	private static function _generate_regex_for_logic()
	{
		$regex 		= '/'.implode( '|', static::$valid_logic ).'/';

		return $regex;
	}

	protected static function parse_expr()
	{
		$regex 			= static::_generate_regex_for_logic();
		$instance 		= static::get_expression_instance();

		$expr 			= static::_filter_by_logic();
		$eval_and 		= array();
		$eval_or  		= array();
		$eval_xor 		= array();

		if( !EMPTY( $expr ) AND !EMPTY( $expr[ self::EXPR_AND ] ) ) 
		{
			$eval_and	= static::process_and_or_xor( $expr[ self::EXPR_AND ], $regex, $instance );
		}

		if( !EMPTY( $expr ) AND !EMPTY( $expr[ self::EXPR_OR ] ) ) 
		{
			$eval_or	= static::process_and_or_xor( $expr[ self::EXPR_OR ], $regex, $instance );
		}

		if( !EMPTY( $expr ) AND !EMPTY( $expr[ self::EXPR_XOR ] ) ) 
		{
			$eval_xor 	= static::process_and_or_xor( $expr[ self::EXPR_XOR ], $regex, $instance );
		}
		
		$check 			= static::_emulate_logic( $eval_and, $eval_or, $eval_xor );

		return  $check;
	}

	protected static function process_and_or_xor( $array, $regex, $instance )
	{
		$eval_arr 				= array();

		foreach ( $array as $logic => $value ) 
		{
			$value 				= trim( strtolower( $value ) );
			
			if( preg_match( $regex, $value, $macthes ) ) 
			{
				$check_not 		= FALSE;

				$match 			= strtolower( $macthes[0] );

				if( preg_match( static::$valid_inverse_logic_symbol, $value, $not ) ) 
				{
					$check_not 	= TRUE;

					$not 		= trim( $not[0] );

					$match 		= explode( $not , $macthes[0] );

					$match 	 	= strtolower( $match[0] );
				}

				$match 			= trim( $match );

				$meth_name 		= static::$logic_method_name[ $match ];

				$eval_arr[]		= call_user_func_array( array( $instance, $meth_name ), array( $check_not, $value, $match ) );
			} 				

		}

		return $eval_arr;
	}

	private static function _filter_by_logic() 
	{
		$filtered_expr 		= array();

		$filter_and 		= array();
		$filter_or 			= array();
		$filter_xor 		= array();

		$filter_main 		= "";

		$pattern 			= self::REGEX_AND.'|'.self::REGEX_OR.'|'.self::REGEX_XOR;

		$main_pattern 		= '/'.$pattern.'/i';

		$and_pattern 		= '/'.self::REGEX_AND.'/i';

		$or_pattern 		= '/'.self::REGEX_OR.'/i';

		$xor_pattern 		= '/'.self::REGEX_XOR.'/i';

		if( preg_match( $main_pattern, static::$expr ) ) 
		{
			$remove_pattern 		= '/'.'('.$pattern.')'.'[\s\S]*'.'/i';

			$filter_main 			= preg_replace( $remove_pattern, '', static::$expr );

			$filter_and[] 			= $filter_main;
		} 
		else 
		{
			$filter_and[]		 	= static::$expr;
		}

		if( preg_match( $and_pattern, static::$expr ) ) 
		{
			$clean_pattern 	= '\s('.self::REGEX_OR.'|'.self::REGEX_XOR.')\s';

			$filter_and 	= static::_filter_and_or_xor( self::REGEX_AND, self::EXPR_AND, $clean_pattern, $filter_main );
		}

		if( preg_match( $or_pattern , static::$expr ) ) 
		{
			$clean_pattern 	= '\s('.self::REGEX_AND.'|'.self::REGEX_XOR.')\s';

			$filter_or 	 	= static::_filter_and_or_xor( self::REGEX_OR, self::EXPR_OR, $clean_pattern, $filter_main );
		}

		if( preg_match( $xor_pattern , static::$expr ) ) 
		{

			$clean_pattern 	= '\s('.self::REGEX_AND.'|'.self::REGEX_OR.')\s';

			$filter_xor 	= static::_filter_and_or_xor( self::REGEX_XOR, self::EXPR_XOR, $clean_pattern, $filter_main );

		}
		
		$filtered_expr[ self::EXPR_AND ] 		= $filter_and;
		$filtered_expr[ self::EXPR_OR ] 		= $filter_or;
		$filtered_expr[ self::EXPR_XOR ] 		= $filter_xor;

		return $filtered_expr;
	}

	private static function _filter_and_or_xor( $remove_regex, $replace_with, $clean_regex, $filter_main )
	{
		$expr 			= static::$expr;

		$ret_arr 		= array();

		$rem_pattern 	= '/\s('.$remove_regex.')\s/i';

		$filtered_expr 	= preg_replace( $rem_pattern , $replace_with, $expr );

		$filtered_expr 	= explode( $replace_with, $filtered_expr );

		$filtered_expr 	= static::_clean_logic( $filtered_expr, $clean_regex );

		if( $replace_with !== self::EXPR_AND ) 
		{
			if( !EMPTY( $filter_main ) ) 
			{
				$filtered_expr = array_slice( $filtered_expr , 1 );
			}

		}

		if( !EMPTY( $filtered_expr ) ) 
		{
			$ret_arr 	= $filtered_expr;
		}

		return $ret_arr;
	}

	private static function _clean_logic( $logic_arr = array(), $pattern = '' )
	{
		$ret_arr 				= array();

		$pattern 				= '/'.$pattern.'[\s\S]*'.'/i';

		if( !EMPTY( $logic_arr ) ) 
		{
			foreach( $logic_arr as $log_key => $log_val ) 
			{
				$clean_logic 	= preg_replace( $pattern, '', $log_val );

				$ret_arr[] 		= $clean_logic;
			}

		}

		return $ret_arr;

	}

	protected function process_empty( $inverse, $value, $match )
	{
		$val_name 	= preg_replace( '/^empty|!empty|not empty/', '', $value );
		
		$val_name 	= trim( $val_name );

		return (bool) ( $inverse ) ? !EMPTY( static::$value[ $val_name ] ) : EMPTY( static::$value[ $val_name ] ) ;
	}

	protected function process_isset( $inverse, $value, $match ) 
	{
		$val_name 	= preg_replace( '/^isset|!isset|not isset/', '', $value );
		
		$val_name 	= trim( $val_name );

		return (bool) ( $inverse ) ? !ISSET( static::$value[ $val_name ] ) : ISSET( static::$value[ $val_name ] ) ;
	}

	protected function process_equals( $inverse, $value, $match )
	{
		$satisfier 			= array();

		if( $inverse ) 
		{
			$value 			= preg_replace( static::$valid_inverse_logic_symbol, '', $value );
		}

		$satisfier 			= explode( $match, $value );

		if( !EMPTY( $satisfier ) ) 
		{
			$first_value 	= trim( $satisfier[0] );

			$second_value 	= trim( $satisfier[1] );

			$first_satis 	= ( ISSET( static::$value[ $first_value ] ) AND !EMPTY( static::$value[ $first_value ] ) ) ? static::$value[ $first_value ] : $first_value;

			$second_satis 	= ( ISSET( static::$value[ $second_value ] ) AND !EMPTY( static::$value[ $second_value ] ) ) ? static::$value[ $second_value ] : $second_value;

			return (bool) ( $inverse ) ? $first_satis != $second_satis : $first_satis == $second_satis;
		}

		return FALSE;

	}

	protected function process_object( $inverse, $value, $match )
	{
		$val_name 		= preg_replace( '/^object|!object|not object/', '', $value );

		$val_name 		= trim( $val_name );

		$obj_arr 		= array();

		$check 			= FALSE;

		$args  			= array();

		if( preg_match( '/./', $val_name ) ) 
		{
			$obj_arr 	= explode( '.', $val_name );

			if( !EMPTY( $obj_arr ) ) 
			{
				$object 	= trim( $obj_arr[0] );

				$object 	= ( !EMPTY( static::$value[ $object ] ) ) ? static::$value[ $object ] : NULL;

				$method 	= trim( $obj_arr[1] );

				$args 		= $this->get_expr_args( $method );

				$method 	= $this->clean_name( $method );

				$call 		= FALSE;

				if( !EMPTY( $object ) ) 
				{
					$method_factory 	= static::get_factory_instance()->get_instance( FALSE, FALSE, TRUE );

					$new_method 		= $method_factory->rules( $object, $method );

					$call 				= $new_method->invokeArgs( $object, $args );
				}

				$check 		= ( bool ) ( $inverse ) ?  !$call : $call; 

			}

		}

		return $check;
	}

	protected function process_validate( $inverse, $value, $match )
	{
		$ret_arr 			= array();

		if( $inverse ) 
		{
			$value 			= preg_replace( '/(^!|not|NOT)\s/', '!', $value );

			$value 			= trim( $value );
		}
		
		$clean_value 		= preg_replace( '/\s\S*/', '', $value );
		
		$rules 				= explode( ',', $clean_value );
		
		$field_arr 			= explode( $clean_value, $value );

		$field 				= trim( $field_arr[1] );

		$ajd_value 			= ( ISSET( static::$value[ $field ] )  ) ? static::$value[ $field ] : NULL;
		
		foreach ( $rules as $rules_key => $rules_value ) 
		{
			$args 			= $this->get_expr_args( $rules_value, TRUE );

			$rules_value 	= $this->clean_name( $rules_value );

			$check_rule 	= preg_replace( static::$valid_inverse_logic_symbol, '', $rules_value );
			
			if( in_array( $check_rule, static::$single_args ) ) 
			{
				$args 		= $args[0];
			}
			
			static::$ajd->addRule( $rules_value, $args );
		}

		static::$ajd->check( $field, $ajd_value );

		$ret_arr[] 			= static::$ajd->validation_fails( $field );
		
		$check 				= ( in_array( 1, $ret_arr ) ) ? FALSE : TRUE;

		return $check;
	}

	protected function expression_has_args( $name )
	{
		$check 				= explode( '(', $name );

		return ( ISSET( $check[1] ) AND !EMPTY( $check[1] ) );
	}

	protected function clean_name( $name )
	{
		if( !$this->expression_has_args( $name ) ) 
		{
			return $name;
		}

		$name_clean 		= preg_replace( '/\([\s\S]*/', '', $name );

		return $name_clean;
	}

	protected function get_expr_args( $name, $dont_get_value = FALSE )
	{
		$ret_args 			= array();

		if( !$this->expression_has_args( $name ) ) 
		{
			return array();
		}

	    list( $meth_name, $args_with_bracket_end ) 	= explode( '(', $name );

        $args  				= rtrim( $args_with_bracket_end, ')' );

        $args 				= preg_replace( '/\s+/', '', $args );

        $args  				= explode( ',', $args );

        if( $dont_get_value ) 
        {
        	$ret_args 		= $args;
        } 
        else 
        {        
    		$ret_args 		= $this->get_value_args( $args );
        }

        return $ret_args;
	}

	protected function get_value_args( array $args )
	{
		$ret_args 		= array();

		if( EMPTY( $args ) ) 
		{
			return $ret_args;
		}

		foreach( $args as $arg_key => $arg_value ) 
		{
			if( ISSET( static::$value[ $arg_value ] ) ) 
			{
				$ret_args[] 	= static::$value[ $arg_value ];
			} 

		}

		return $ret_args;
	}

	private static function _emulate_logic( $and_arr = array(), $or_arr = array(), $xor_arr = array() )
	{	
		$and_check 		= !in_array( 0, $and_arr );

		$or_check		= in_array( 1, $or_arr );

		if( COUNT( $xor_arr ) === 1 ) 
		{
			$xor_check 		= in_array( 1, $xor_arr );
		} 
		else 
		{
			$xor_check 		= ( ( in_array( 0, $xor_arr ) OR !in_array( 1, $xor_arr ) ) AND ( !in_array( 0, $xor_arr ) OR in_array( 1, $xor_arr ) ) );
		}

		if( !EMPTY( $or_arr ) OR !EMPTY( $xor_arr ) ) 
		{
			if( !EMPTY( $and_check ) ) 
			{
				if( !EMPTY( $xor_arr ) ) 
				{
					return ( $and_check XOR $xor_check );
				} 
				else 
				{
					return $and_check;
				}
			} 
			else 
			{
				if( !EMPTY( $xor_arr ) ) 
				{
					return ( $or_check XOR $xor_check );
				} 
				else 
				{
					return $or_check;
				}
			}
		} 
		else 
		{
			return $and_check;
		}
	}
}

