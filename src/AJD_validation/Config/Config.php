<?php namespace AJD_validation\Config;

class Config
{
	protected static $default_dir_path;
	protected static $file;
	protected static $default = null;

	public function __construct( $file = null, $dir = null )
	{
		static::setConfigFile( $file, $dir );
	}

	public static function setConfigFile( $file = null, $dir = null )
	{
		static::$default_dir_path = dirname( __FILE__ ).DIRECTORY_SEPARATOR;
		
		if( !is_null( $dir ) ) 
		{
			static::$default_dir_path = $dir;		
		}

		if( !empty( $file ) && file_exists( static::$default_dir_path.$file ) )
		{
			static::$file = require static::$default_dir_path.$file;
		}
	}

	public static function getConfigFile( $file = null, $dir = null )
	{
		$default_dir_path = dirname( __FILE__ ).DIRECTORY_SEPARATOR;

		if( !is_null( $dir ) ) 
		{
			$default_dir_path = $dir;		
		}

		if( file_exists( $default_dir_path.$file ) )
		{
			return require $default_dir_path.$file;
		}
	}

	public static function get( $key, $default = null, $file_data = null )	
	{
		static::$default = $default;
		$segments = explode( '.', $key );
		$file = ( !empty( $file_data ) ) ? $file_data : static::$file;

		foreach( $segments as $item ) 
		{
			if( isset( $file[ $item ] ) ) 
			{
				$file = $file[ $item ];
			} 
			else 
			{
				$file = static::$default;
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

