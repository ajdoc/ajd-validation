<?php namespace AJD_validation\Config;

class Config
{
	protected static $default_dir_path;
	protected static $file;
	protected static $default = NULL;

	public function __construct( $file = NULL, $dir = NULL )
	{
		static::setConfigFile( $file, $dir );
	}

	public static function setConfigFile( $file = NULL, $dir = NULL )
	{
		static::$default_dir_path 		= dirname( __FILE__ ).DIRECTORY_SEPARATOR;
		
		if( !IS_NULL( $dir ) ) 
		{
			static::$default_dir_path 	= $dir;		
		}

		if( !EMPTY( $file ) AND file_exists( static::$default_dir_path.$file ) )
		{
			static::$file 					= require static::$default_dir_path.$file;
		}
	}

	public static function getConfigFile( $file = NULL, $dir = NULL )
	{
		$default_dir_path 				= dirname( __FILE__ ).DIRECTORY_SEPARATOR;

		if( !IS_NULL( $dir ) ) 
		{
			$default_dir_path 			= $dir;		
		}

		if( file_exists( $default_dir_path.$file ) )
		{
			return require $default_dir_path.$file;
		}
	}

	public static function get( $key, $default = NULL, $file_data = NULL )	
	{
		static::$default 			= $default;
		$segments					= explode( '.', $key );
		$file 						= ( !EMPTY( $file_data ) ) ? $file_data : static::$file;

		foreach( $segments as $item ) 
		{
			if( ISSET( $file[ $item ] ) ) 
			{
				$file 				= $file[ $item ];
			} 
			else 
			{
				$file 				= static::$default;

				break;
			}

		}

		return $file;
	}

	public static function exists( $key )
	{
		return ( static::get( $key ) !== static::$default );
	}

}

