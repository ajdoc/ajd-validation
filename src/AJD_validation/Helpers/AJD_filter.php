<?php namespace AJD_validation\Helpers;

use AJD_validation\Factory\Factory_strategy;
use AJD_validation\Contracts\Base_validator;

class AJD_filter extends Base_validator 
{

	const DS 							= DIRECTORY_SEPARATOR;

	protected static $filter_path;

	protected static $filter 			= array();
	protected static $value;
	protected static $field;
	protected static $satisfier			= array();
	protected static $pre_filter 		= array();
	protected static $extension_filter 	= array();

	protected static $filter_value	 	= array();
	protected static $pre_filter_value 	= array();

	protected static $filter_suffix 	= 'filter';

	protected static $cache_instance 	= array();

	protected static $addFilterNamespace	= array();
	protected static $addFilterDirectory 	= array();

	public function get_filter_path()
	{
		static::$filter_path 			= dirname( dirname( __FILE__ ) ).self::DS.'Filters'.self::DS;

		return static::$filter_path;
	}

	public function addFilterNamespace( $namespace )
	{
		array_push( static::$addFilterNamespace, $namespace );

		return $this;
	}

	public function addFilterDirectory( $namespace )
	{
		array_push( static::$addFilterDirectory, $namespace );

		return $this;
	}

	public function set_filter( $filter, $value, $field, $satisfier, $pre_filter, array $extension_filter = array() )
	{
		static::$extension_filter 	= $extension_filter;
		static::$filter 	= $filter;
		static::$value  	= $value;

		$field_arr 			= $this->format_field_name( $field );
		$field 				= strtolower( $field_arr[ 'clean' ] );
		$orig_field 		= $field_arr[ 'orig' ];

		static::$field 		= $field;
		static::$satisfier 	= $satisfier;
		static::$pre_filter = $pre_filter;
	}

	public function filter( $check_arr = TRUE, $val_only = FALSE )
	{
		$filter_arr 	= static::$filter;
		$real_val 		= NULL;
		
		if( !EMPTY( static::$value ) ) 
		{
			foreach( $filter_arr as $fil_key => $fil_value ) 
			{
				$filter 	= $fil_value.'_'.static::$filter_suffix;

				$field 		= static::$field;

				$pre_fil 	= ( $this->isset_empty( static::$pre_filter, $fil_key ) ) ? TRUE : FALSE;

				if( $val_only )
				{
					$value 		= static::$value;
				}
				else
				{
					if( $pre_fil ) 
					{
						$value 	= ( ISSET( static::$pre_filter_value[ $field ] ) ) ? static::$pre_filter_value[ $field ] : static::$value;
					} 
					else 
					{
						$value 	= ( ISSET( static::$filter_value[ $field ] ) ) ? static::$filter_value[ $field ] : static::$value;	
					}

				}

				$satis 		= static::$satisfier[ $fil_key ];

				if( is_array( $value ) AND $check_arr ) 
				{
					$value 	= $this->flattened_array( $value );

					foreach ( $value as $k_val => $v_val ) 
					{
						$real_val = $this->_process_filter( $fil_value, $filter, $v_val, $satis, $field, $pre_fil, TRUE, $k_val, $val_only );	
					}
				} 
				else 
				{
					$real_val 		= $this->_process_filter( $fil_value, $filter, $value, $satis, $field, $pre_fil, FALSE, NULL, $val_only );
				}

				// call_user_func_array( array( $this, $filter ) , array( $value, $satis, $field ) );
			}
		}
		
		if( $val_only )
		{
			return $real_val;
		}

	}

	private function _process_filter( $filter, $append_filter, $value, $satisfier, $field, $pre_filter, $check_arr, $counter = NULL, $val_only = FALSE )
	{

		$class_filt 		= ucfirst( strtolower( $append_filter ) );	

		$filter_path 		= $this->get_filter_path().$class_filt.'.php';

		if( !EMPTY( static::$addFilterDirectory ) )
		{
			foreach( static::$addFilterDirectory as $classPath )
			{
				$requiredFiles 	= get_required_files();

				$pathHolder 	= $classPath.$class_filt.'.php';

				$search 		= array_search($pathHolder, $requiredFiles);

				if( file_exists( $pathHolder ) AND EMPTY( $search ) )
				{
					$filter_path 	= $pathHolder;
				}	
			}
		}

		$is_class 			= file_exists( $filter_path );

		$is_function 		= function_exists( $filter );

		$is_method 			= method_exists( $this , $append_filter );

		$is_extension 		= FALSE;

		if( !EMPTY( static::$extension_filter ) AND ISSET( static::$extension_filter[ $append_filter ] ) )
		{
			$is_extension 	= TRUE;
		}

		if( $is_extension )  
		{
			$extension_filter 	= static::$extension_filter[ $append_filter ];

			$real_val			= $this->_process_extension( $extension_filter, $append_filter, $filter, $is_extension, $field, $value, $satisfier, $pre_filter, $check_arr, $counter, $val_only );
		}
		else if( $is_class ) 
		{
			$real_val 			= $this->_process_class( $class_filt, $filter, $filter_path, $is_class, $field, $value, $satisfier, $pre_filter, $check_arr, $counter, $val_only );			
		} 
		else if( $is_function ) 
		{
			$real_val 			= $this->_process_function( $filter, $value, $satisfier, $field, $is_function, $pre_filter, $check_arr, $counter, $val_only );
		} 
		else if( $is_method ) 
		{
			$real_val 			= $this->_process_method( $append_filter, $filter, $field, $value, $satisfier, $is_method, $pre_filter, $check_arr, $counter, $val_only );
		}

		if( $val_only AND ISSET( $real_val ) )
		{
			return $real_val;
		}

	}

	private function _process_extension( array $extension_filter, $append_filter, $filter, $is_extension, $field, $value, $satisfier, $pre_filter, $check_arr, $counter, $val_only = FALSE )
	{
		if( ISSET( $extension_filter['extension_obj'] ) )
		{
			$extension_class_name 	= get_class( $extension_filter['extension_obj'] );
			$extension_obj 			= $extension_filter['extension_obj'];

			// $filter_ins 			= static::get_filter_ins();

			$method_factory 		= static::get_factory_instance()->get_instance( FALSE, FALSE, TRUE );

			$method 				= $method_factory->rules( $extension_class_name, $append_filter );

			$new_value				= $method_factory->process_method( array( $value, $satisfier, $field ), $extension_obj, TRUE );

			$real_val 				= $this->_process_filter_values( $field, $new_value, $check_arr, $pre_filter, $counter, $val_only );

			if( $val_only )
			{
				return $real_val;
			}
		}
	}

	private function _process_class( $filter, $filter_name, $filter_path, $is_class, $field, $value, $satisfier, $pre_filter, $check_arr, $counter, $val_only = FALSE )
	{

		/*if( !ISSET( static::$cache_instance[ $filter ] ) ) 
		{*/
			$class_factory 		= static::get_factory_instance()->get_instance( $is_class );

			if( !EMPTY( static::$addFilterNamespace ) )
			{
				$this->_appendFilterNameSpace( $class_factory );
			}

			$classArgs 			= ( IS_NULL( $satisfier ) ) ? array() : $satisfier;
			
			if( !is_array( $classArgs ) )
			{
				$classArgs 		= array( $classArgs );
			}

			$filter_obj 		= $class_factory->rules( $filter_path, $filter, $classArgs, TRUE );
		/*} 
		else 
		{
			$filter_obj 		= static::$cache_instance[ $filter ];
		}*/

		static::$cache_instance[ $filter ] 	= $filter_obj;
		
		$new_value 				= $filter_obj->filter( $value, $satisfier, $field );

		$real_val 				= $this->_process_filter_values( $field, $new_value, $check_arr, $pre_filter, $counter, $val_only );		

		if( $val_only )
		{
			return $real_val;
		}

	}

	private function _appendFilterNameSpace( $classFactory )
	{
		foreach( static::$addFilterNamespace as $filterNamespace )
		{
			$classFactory->append_filter_namespace( $filterNamespace );
		}
	}

	private function _process_function( $filter_name, $value, $satisfier, $field, $is_function, $pre_filter, $check_arr, $counter= NULL, $val_only = FALSE )
	{
		$function_factory 		= static::get_factory_instance()->get_instance( FALSE, $is_function );

		if( $function_factory->func_valid( $filter_name ) ) 
		{
			$func 				= $function_factory->rules( $filter_name );

			$new_value 			= $function_factory->process_function( $field, $value, $satisfier );

			$real_val 			= $this->_process_filter_values( $field, $new_value, $check_arr, $pre_filter, $counter, $val_only );


			if( $val_only )
			{
				return $real_val;
			}
		}
	}

	private function _process_method( $append_filter, $filter, $field, $value, $satisfier, $is_method, $pre_filter, $check_arr, $counter, $val_only = FALSE )
	{
		$filter_ins 			= static::get_filter_ins();

		$method_factory 		= static::get_factory_instance()->get_instance( FALSE, FALSE, $is_method );

		$method 				= $method_factory->rules( __CLASS__, $append_filter );

		$new_value				= $method_factory->process_method( array( $value, $satisfier, $field ), $filter_ins, TRUE );

		$real_val 				= $this->_process_filter_values( $field, $new_value, $check_arr, $pre_filter, $counter, $val_only );

		if( $val_only )
		{
			return $real_val;
		}
	}

	private function _process_filter_values( $field, $val, $check_arr, $pre_filter, $counter, $val_only = FALSE )
	{
		if( $pre_filter ) 
		{
			if( $check_arr ) 
			{
				static::$pre_filter_value[ $field ][ $counter ] 	= $val;
			} 
			else 
			{
				static::$pre_filter_value[ $field ] 				= $val;	
			}

		} 
		else 
		{
			if( $check_arr ) 
			{
				if(isset(static::$filter_value[ $field ][ $counter ]))
				{
					static::$filter_value[ $field ][ $counter ] 		= $val;
				}
			} 
			else 
			{
				static::$filter_value[ $field ] 					= $val;
			}
		}

		if( $val_only )
		{
			return $val;
		}
	}

	public function get_filtered_value( $key = NULL )
	{
		$filt_value 		= $this->_get_filtered_value( static::$filter_value, $key );

		return $filt_value;
	}

	public function get_pre_filter_value( $key = NULL )
	{
		$filt_value 		= $this->_get_filtered_value( static::$pre_filter_value, $key );

		return $filt_value;
	}

	private function _get_filtered_value( $arr, $key = NULL )
	{
		$filt_value 		= $arr;

		if( !IS_NULL( $key ) ) 
		{
			if( ISSET( $arr[ $key ] ) ) 
			{
				$filt_value = $arr[ $key ];
			}
		}

		return $filt_value;

	}

	protected function filter_callback_filter( $field, $value, $satisfier )
	{
		return $satisfier( $field, $value );
	}

}