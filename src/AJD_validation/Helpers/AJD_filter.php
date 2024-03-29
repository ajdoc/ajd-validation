<?php namespace AJD_validation\Helpers;

use AJD_validation\Factory\Factory_strategy;
use AJD_validation\Contracts\Base_validator;
use AJD_validation\Contracts\AbstractAnonymousFilter;
use AJD_validation\Helpers\Filters_map;
use AJD_validation\Factory\Class_factory;

class AJD_filter extends Base_validator 
{
	const DS = DIRECTORY_SEPARATOR;

	protected static $filter_path;

	protected static $filter = [];
	protected static $value;
	protected static $field;
	protected static $append;
	protected static $satisfier = [];
	protected static $pre_filter = [];
	protected static $extension_filter = [];

	protected static $filter_value = [];
	protected static $pre_filter_value = [];

	public static $filter_suffix = 'filter';

	protected static $cache_instance = [];

	protected static $addFilterNamespace = [];
	protected static $addFilterDirectory = [];

	protected static $addFiltersMappings = [];

	protected static $customFilters = [];

	public static function registerFilter($name, $function, array $extraArgs = [])
	{
		if(isset(static::$customFilters[$name]))
		{
			return;
		}

		if(is_callable($function))
		{
			if(is_object($function) && $function instanceof AbstractAnonymousFilter)
			{
				if(!method_exists($function, '__invoke') && !method_exists($function, 'filter'))
				{
					throw new \InvalidArgumentException('Anonymous filter must have __invoke method or filter method.');
				}

				static::plotAnonymousFilter($name, $function, $extraArgs);

				return;
			}

			static::createAnonymousFilter($name, $function, $extraArgs);
		}

		return;
	}

	protected static function plotAnonymousFilter($name, $function, array $extraArgs = [])
	{
		static::$customFilters[$name]['function'] = $function;
		static::$customFilters[$name]['extraArgs'] = $extraArgs;
	}

	protected static function createAnonymousFilter($name, $function, array $extraArgs = [])
	{
		$anonClass = new class($function) extends AbstractAnonymousFilter
		{
			protected $callback;
			protected $extraArgs;

			public function __construct($callback, array $extraArgs = [])
			{
				$this->callback = $callback;
				$this->extraArgs = $extraArgs;
			}

			public function __invoke($value, $satisfier = null, $field = null)
			{
				$callback = $this->callback;

				if($callback instanceof \Closure)
            	{
            		$callback = $callback->bindTo($this, self::class);
            	}

            	$args = array_merge(func_get_args(), $this->extraArgs);

            	return \call_user_func_array($callback, $args);
			}
		};

		static::plotAnonymousFilter($name, $anonClass, $extraArgs);
	}

	public static function registerFiltersMappings(array $mappings)
	{
		foreach($mappings as $filterKey => $filter)
        {
            Filters_map::register($filter);
            Filters_map::setFilter($filter);
        }

        static::processMappings();
	}

	public static function processMappings()
	{
		$mappings = Filters_map::getMappings();
		
		if($mappings)
		{
			static::$addFiltersMappings = array_merge(static::$addFiltersMappings, $mappings);

			Class_factory::addFiltersMappings($mappings);

			Filters_map::flush();
		}
	}

	public function get_filter_path()
	{
		static::$filter_path = dirname( dirname( __FILE__ ) ).self::DS.'Filters'.self::DS;

		return static::$filter_path;
	}

	public function addFilterNamespace( $namespace )
	{
		array_push( static::$addFilterNamespace, $namespace );

		return $this;
	}

	public function addFilterDirectory( $directory )
	{
		array_push( static::$addFilterDirectory, $directory );

		return $this;
	}

	public function set_filter( $filter, $value, $field, $satisfier, $pre_filter, array $extension_filter = [], $append = false )
	{
		static::$extension_filter = $extension_filter;
		static::$filter = $filter;
		static::$value = $value;

		$field_arr = $this->format_field_name( $field );
		$field = strtolower( $field_arr[ 'clean' ] );
		$orig_field = $field_arr[ 'orig' ];

		static::$field = $orig_field;
		static::$satisfier = $satisfier;
		static::$pre_filter = $pre_filter;
		static::$append = $append;
	}

	public function filter( $check_arr = true, $val_only = false )
	{
		$filter_arr = static::$filter;
		$real_val = NULL;

		$v = static::$value;

		if( !empty( static::$value ) ) 
		{
			foreach( $filter_arr as $fil_key => $fil_value ) 
			{

				$filter = $fil_value.'_'.static::$filter_suffix;

				$field = static::$field;

				$pre_fil = ( $this->isset_empty( static::$pre_filter, $fil_key ) ) ? true : false;

				$origValue = null;

				if(( is_array(static::$value) || is_object(static::$value) ) && !$check_arr)
				{
					$origValue = static::$value;
				}

				if( $val_only )
				{
					$value = static::$value;
				}
				else
				{
					if( $pre_fil ) 
					{
						$value = ( isset( static::$pre_filter_value[ $field ] ) ) ? static::$pre_filter_value[ $field ] : static::$value;
					} 
					else 
					{
						$value = ( isset( static::$filter_value[ $field ] ) ) ? static::$filter_value[ $field ] : static::$value;	
					}
				}

				if(static::$append)
				{
					if($fil_key == 0)
					{
						$v = $value;
					}
				}
				else
				{
					$v = $value;
				}
				
				$satis = static::$satisfier[ $fil_key ];

				if( is_array( $v ) && $check_arr ) 
				{
					$v 	= $this->flattened_array( $v );

					$cnt = 0;
					
					foreach ( $v as $k_val => $v_val ) 
					{
						$rv = $this->_process_filter( $fil_value, $filter, $v_val, $satis, $field, $pre_fil, true, $k_val, $val_only, $origValue );

						$real_val[$k_val] = $rv;	

						$v[$k_val] = $rv;
					}
				} 
				else 
				{
					$real_val = $this->_process_filter( $fil_value, $filter, $v, $satis, $field, $pre_fil, false, null, $val_only, $origValue );

					$v = $real_val;
				}
				// call_user_func_array( array( $this, $filter ) , array( $value, $satis, $field ) );
			}
		}
		
		if( $val_only )
		{
			return $real_val;
		}

	}

	private function _process_filter( $filter, $append_filter, $value, $satisfier, $field, $pre_filter, $check_arr, $counter = null, $val_only = false, $origValue = null )
	{
		$class_filt = ucfirst( strtolower( $append_filter ) );	
		$lower_filter = strtolower($class_filt);
		$filter_path = $this->get_filter_path().$class_filt.'.php';

		if( !empty( static::$addFilterDirectory ) )
		{
			foreach( static::$addFilterDirectory as $classPath )
			{
				$pathHolder = $classPath.$class_filt.'.php';

				if( file_exists( $pathHolder ) )
				{
					$filter_path = $pathHolder;
				}	
			}
		}

		$is_class = file_exists( $filter_path );

		if(!$is_class)
		{
			if(!empty(static::$addFiltersMappings))
			{
				if(isset(static::$addFiltersMappings[$lower_filter]))
				{
					$is_class = true;
				}
			}
		}
		
		$is_function = (!empty($filter)) ? function_exists( $filter ) : false;

		$is_anon = false;

		if(
			!empty($filter)
			&&
			isset(static::$customFilters[$filter])
			&&
			!empty(static::$customFilters[$filter])
		)
		{
			$is_anon = true;
		}

		$is_method = method_exists( $this , $append_filter );

		$is_extension = false;

		if( !empty( static::$extension_filter ) && isset( static::$extension_filter[ $append_filter ] ) )
		{
			$is_extension = true;
		}

		if( $is_extension )  
		{
			$extension_filter = static::$extension_filter[ $append_filter ];

			$real_val = $this->_process_extension( $extension_filter, $append_filter, $filter, $is_extension, $field, $value, $satisfier, $pre_filter, $check_arr, $counter, $val_only, $origValue );
		}
		else if( $is_class ) 
		{
			$real_val = $this->_process_class( $class_filt, $filter, $filter_path, $is_class, $field, $value, $satisfier, $pre_filter, $check_arr, $counter, $val_only, $origValue );			
		} 
		else if( $is_anon )
		{
			$real_val = $this->_process_anon_class($class_filt, $filter, $filter_path, $is_class, $field, $value, $satisfier, $pre_filter, $check_arr, $counter, $val_only, $origValue);
		}
		else if( $is_function ) 
		{
			$real_val = $this->_process_function( $filter, $value, $satisfier, $field, $is_function, $pre_filter, $check_arr, $counter, $val_only, $origValue );
		} 
		else if( $is_method ) 
		{
			$real_val = $this->_process_method( $append_filter, $filter, $field, $value, $satisfier, $is_method, $pre_filter, $check_arr, $counter, $val_only, $origValue );
		}

		if( $val_only && ISSET( $real_val ) )
		{
			return $real_val;
		}
	}

	private function _process_anon_class( $filter, $filter_name, $filter_path, $is_class, $field, $value, $satisfier, $pre_filter, $check_arr, $counter, $val_only = false, $origValue = null )
	{
		$filtDetails = static::$customFilters[$filter_name];

		$filter_obj = $filtDetails['function'];
		$filter_obj->setExtraArgs($filtDetails['extraArgs']);

		static::$cache_instance[ $filter ] = $filter_obj;

		$new_value = null;

		if(!empty($value))
		{
			$new_value = \call_user_func_array($filter_obj, [$value, $satisfier, $field, $filter_obj]);
		}
		
		$real_val = $this->_process_filter_values( $field, $new_value, $check_arr, $pre_filter, $counter, $val_only );		
		
		if( $val_only )
		{
			return $real_val;
		}
	}

	private function _process_extension( array $extension_filter, $append_filter, $filter, $is_extension, $field, $value, $satisfier, $pre_filter, $check_arr, $counter, $val_only = false, $origValue = null )
	{
		if( ISSET( $extension_filter['extension_obj'] ) )
		{
			$extension_class_name = get_class( $extension_filter['extension_obj'] );
			$extension_obj = $extension_filter['extension_obj'];

			// $filter_ins 			= static::get_filter_ins();

			$method_factory = static::get_factory_instance()->get_instance( FALSE, FALSE, TRUE );

			$method = $method_factory->rules( $extension_class_name, $append_filter );

			$new_value = null;

			if(!empty($value))
			{
				$new_value = $method_factory->process_method( array( $value, $satisfier, $field ), $extension_obj, TRUE );
			}

			$real_val = $this->_process_filter_values( $field, $new_value, $check_arr, $pre_filter, $counter, $val_only );

			if( $val_only )
			{
				return $real_val;
			}
		}
	}

	private function _process_class( $filter, $filter_name, $filter_path, $is_class, $field, $value, $satisfier, $pre_filter, $check_arr, $counter, $val_only = false, $origValue = null )
	{

		/*if( !ISSET( static::$cache_instance[ $filter ] ) ) 
		{*/
			$class_factory = static::get_factory_instance()->get_instance( $is_class );

			if( !EMPTY( static::$addFilterNamespace ) )
			{
				$this->_appendFilterNameSpace( $class_factory );
			}

			$classArgs = ( is_null( $satisfier ) ) ? [] : $satisfier;
			
			if( !is_array( $classArgs ) )
			{
				$classArgs = array( $classArgs );
			}

			$filter_obj = $class_factory->rules( $filter_path, $filter, $classArgs, TRUE );
		/*} 
		else 
		{
			$filter_obj 		= static::$cache_instance[ $filter ];
		}*/

		static::$cache_instance[ $filter ] = $filter_obj;

		$new_value = null;

		if(!empty($value))
		{
			$new_value = $filter_obj->filter( $value, $satisfier, $field );
		}
		
		$real_val = $this->_process_filter_values( $field, $new_value, $check_arr, $pre_filter, $counter, $val_only );		
		
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

	private function _process_function( $filter_name, $value, $satisfier, $field, $is_function, $pre_filter, $check_arr, $counter= NULL, $val_only = FALSE, $origValue = null )
	{
		$function_factory = static::get_factory_instance()->get_instance( FALSE, $is_function );

		if( $function_factory->func_valid( $filter_name ) ) 
		{
			$func = $function_factory->rules( $filter_name );

			$new_value = null;

			if(!empty($value))
			{
				$new_value = $function_factory->process_function( $field, $value, $satisfier );
			}

			$real_val = $this->_process_filter_values( $field, $new_value, $check_arr, $pre_filter, $counter, $val_only );

			if( $val_only )
			{
				return $real_val;
			}
		}
	}

	private function _process_method( $append_filter, $filter, $field, $value, $satisfier, $is_method, $pre_filter, $check_arr, $counter, $val_only = FALSE, $origValue = null )
	{
		$filter_ins = static::get_filter_ins();

		$method_factory = static::get_factory_instance()->get_instance( FALSE, FALSE, $is_method );

		$method = $method_factory->rules( __CLASS__, $append_filter );

		$new_value = null;

		if(!empty($value))
		{
			$new_value = $method_factory->process_method( array( $value, $satisfier, $field ), $filter_ins, TRUE );
		}

		$real_val = $this->_process_filter_values( $field, $new_value, $check_arr, $pre_filter, $counter, $val_only );

		if( $val_only )
		{
			return $real_val;
		}
	}

	private function _process_filter_values( $field, $val, $check_arr, $pre_filter, $counter, $val_only = FALSE )
	{
		if( $pre_filter ) 
		{
			if( $check_arr && !is_null($counter) ) 
			{
				if(!isset(static::$pre_filter_value[ $field ]))
				{
					static::$pre_filter_value[ $field ] = [];
				}
				else
				{
					if(!is_array(static::$pre_filter_value[ $field ]))
					{
						$pre_filt = static::$pre_filter_value[ $field ];

						static::$pre_filter_value[ $field ] = [];

						static::$pre_filter_value[ $field ][ $counter ] = $pre_filt;
					}
				}

				static::$pre_filter_value[ $field ][ $counter ] = $val;	
			} 
			else 
			{
				if(isset(static::$pre_filter_value[ $field ]))
				{
					if(is_array(static::$pre_filter_value[ $field ]))
					{

					}
					else
					{
						static::$pre_filter_value[ $field ] = $val;	
					}
				}
				else
				{
					static::$pre_filter_value[ $field ] = $val;		
				}
			}

		} 
		else 
		{
			if( $check_arr && !is_null($counter) ) 
			{
				if(!isset(static::$filter_value[ $field ]))
				{
					static::$filter_value[ $field ] = [];
				}
				else
				{
					if(!is_array(static::$filter_value[ $field ]))
					{
						$filt = static::$filter_value[ $field ];

						static::$filter_value[ $field ] = [];

						static::$filter_value[ $field ][ $counter ] = $filt;
					}
					
				}
				
				static::$filter_value[ $field ][ $counter ] = $val;	

				/*if(isset(static::$filter_value[ $field ]))
				{
					if(!is_array(static::$filter_value[ $field ]))
					{
						static::$filter_value[ $field ] = [
							$counter => $val
						];
					}
					else
					{
						static::$filter_value[ $field ][$counter] 		= $val;
					}
				}
				*/
			} 
			else 
			{
				if(isset(static::$filter_value[ $field ]))
				{
					if(is_array(static::$filter_value[ $field ]))
					{

					}
					else
					{
						static::$filter_value[ $field ] = $val;	
					}
				}
				else
				{
					static::$filter_value[ $field ] = $val;		
				}
			}
		}

		if( $val_only )
		{
			return $val;
		}
	}

	public function get_filtered_value( $key = null )
	{
		$filt_value = $this->_get_filtered_value( static::$filter_value, $key );

		return $filt_value;
	}

	public function get_pre_filter_value( $key = null )
	{
		$filt_value = $this->_get_filtered_value( static::$pre_filter_value, $key );

		return $filt_value;
	}

	private function _get_filtered_value( $arr, $key = null )
	{
		$filt_value = $arr;

		if( !IS_NULL( $key ) ) 
		{
			if( ISSET( $arr[ $key ] ) ) 
			{
				$filt_value = $arr[ $key ];
			}
			else
			{
				$filt_value = null;
			}
		}

		return $filt_value;

	}

	protected function filter_callback_filter( $field, $value, $satisfier )
	{
		return $satisfier( $field, $value );
	}

}