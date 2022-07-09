<?php namespace AJD_Autoload;

use \RecursiveDirectoryIterator;
use \RecursiveIteratorIterator;

/**
 * Class Loady
 * @package Loady
 * @author Allen Doctor <thedoctorisin17@gmail.com>
 */
Class Loady
{

    const   DS                          = DIRECTORY_SEPARATOR;
    
    private static $_cache_file;

    protected static $recur_dir;
    protected static $recur_iter;

    protected static $cache_load_file   = array();

    protected $psr4             = array();
    protected $classmap         = array();
    protected $files            = array();

    protected $except_class     = array();
    protected $other_ext        = array( '.class', '.inc' );

    private $_dir_root;
    private $_file_ext          = '.php';
    private $_path_arr          = array();
    private $_path;
    
    private $_cache_file_arr    = array();
    private $_cache;
    private $_cache_class       = array();

    public function __construct( $dir_root = NULL )    
    {

        if( !EMPTY( $dir_root ) )
        {
            $this->initialize( $dir_root );
        }    

    }

    /**
     * @param $dir_root
     */
    public function initialize( $dir_root )
    {
        $this->_dir_root        = $dir_root;

        static::$_cache_file    = ( !EMPTY( $this->_dir_root ) ) ? $this->_dir_root.self::DS.'cache/load.cache' : NULL;
    }

    /**
     * @param $path
     * @param bool|FALSE $not_once
     * @return mixed
     */
    protected static function recursiveDir( $path, $not_once = FALSE )
    {

        if( IS_NULL( static::$recur_dir ) OR $not_once ) 
        {
            static::$recur_dir  = new RecursiveDirectoryIterator( $path );
        }

        return static::$recur_dir;

    }

    /**
     * @param $iter
     * @param bool|FALSE $not_once
     * @return mixed
     */
    protected static function recursiveIterator( $iter, $not_once = FALSE )
    {

        if( IS_NULL( static::$recur_iter ) OR $not_once ) 
        {
            static::$recur_iter     = new RecursiveIteratorIterator( $iter );
        }

        return static::$recur_iter;

    }

    /**
     * @param array $path
     */
    public function setPsr4( array $path )
    {
        $this->psr4                 = $path;
    }

    /**
     * @param $namespace
     * @param $path
     */
    public function addPsr4( $namespace, $path )
    {
        $this->psr4[ $namespace ]   = $path;
    }

    /**
     * @param array $path
     */
    public function setClassMap( array $path )
    {
        $this->classmap             = $path;
    }

    /**
     * @param $class
     * @param $path
     */
    public function addClassMap( $class, $path )
    {
        $this->classmap[ $class ]   = $path;
    }

    /**
     * @param array $files
     */
    public function setFiles( array $files )
    {
        $this->files                    = $files;
    }

    /**
     * @param $file
     */
    public function addFile( $file )
    {
        $this->files[]                  = $file;        
    }

    /**
     * @param $path
     */
    public function setAuto( $path )
    {

        if( !is_array( $path ) ) 
        {
            $this->_path            = $this->processDirectory( $path );
        } 
        else 
        {
            $this->_multi_path( $path );
        }

    }

    /**
     * @return array
     */
    public function getAuto()
    {

        if( !EMPTY( $this->_path ) ) 
        {
            return $this->_path;
        } 
        else 
        {
            return $this->_path_arr;
        }

    }

    /**
     * @param $file_ext
     */
    public function setFileExt( $file_ext )
    {
        $this->_file_ext    = $file_ext;
    }

    /**
     * @return string
     */
    public function getFileExt()
    {
        return $this->_file_ext;
    }

    /**
     * @param array $classes
     */
    public function setExceptClass( array $classes )
    {
        $this->except_class = $classes;
    }

    /**
     * @param $class
     */
    public function addExceptClass( $class )
    {
        $this->except_class[]   = $class;
    }

    /**
     * @return array
     */
    public function getExceptClass()
    {
        return $this->except_class;
    }

    /**
     * @param $ext
     */
    public function addOtherExt( $ext )
    {
        $this->other_ext[]      = $ext;
    }

    /**
     * @return array
     */
    public function getOtherExt()
    {
        return $this->other_ext;
    }

    /**
     * @param bool|TRUE $prepend
     * @param bool|TRUE $cache
     */
    public function register( $prepend = TRUE, $cache = TRUE )
    {

        $this->_cache       = $cache;

        spl_autoload_register( array( $this, 'loadClass' ), TRUE, $prepend );

        if( !EMPTY( $this->files ) )
        {
            $this->loadFiles();
        }

    }

    /**
     *
     */
    public function unregister()
    {

        spl_autoload_unregister( array( $this, 'loadClass') );

    }

    /**
     * @param $cache_file
     */
    public function setCacheFile( $cache_file )
    {

        static::$_cache_file    = ( !EMPTY( $this->_dir_root ) ) ? $this->_dir_root.self::DS.$cache_file : $cache_file;

    }

    /**
     * @return mixed
     */
    public function getCacheFile()
    {

        return static::$_cache_file;

    }

    /**
     * @return array|mixed
     */
    public function extractCacheData()
    {

        $extracted_data         = array();

        $extracted_data         = ( file_exists( static::$_cache_file ) ) ? unserialize( file_get_contents( static::$_cache_file ) ) : array();

        return $extracted_data;

    }

    /**
     * @param $cache_file_path
     */
    public function clearCacheFile( $cache_file_path = NULL )
    {

        $cache_file         = ( !EMPTY( $cache_file_path ) ) ? $cache_file_path : static::$_cache_file;

        if( file_exists( $cache_file ) ) 
        {

            file_put_contents( static::$_cache_file, "" );

        }


    }

    /**
     * @param $class
     */
    public function loadClass( $class )
    {
        
        $raw_class          = $class;

        $class              = $this->_clean_class( $class );
        
        $class_filename     = $class.$this->_file_ext;

        $extracted_cache_data     = $this->extractCacheData();

        $this->_cache_file_arr     = ( !EMPTY( $extracted_cache_data ) ) ? $extracted_cache_data : array();

        $cache_data         = $this->_cache_file_arr;

        $except_class       = $this->except_class;

        if ( array_key_exists( $class, $cache_data ) ) 
        {

            if ( file_exists( $cache_data[ $class ] ) ) 
            { 
                $requiredFiles  = get_included_files();

                $search         = array_search($cache_data[ $class ], $requiredFiles);
                
                if( EMPTY( $search ) )
                {
                    require $cache_data[ $class ]; 
                }

            }

        } 
        else 
        {

            if( !EMPTY( $this->_path ) ) 
            {

                $this->loadPath( $this->_path, $class_filename, $class );

            } 
            else 
            {

                if( !EMPTY( $this->_path_arr ) )
                {

                    foreach( $this->_path_arr as $path_key => $path_value ) 
                    {

                        $this->loadPath( $path_value, $class_filename, $class );

                    }

                }

            }   

            if( !EMPTY( $this->psr4 ) )   
            {
                foreach( $this->psr4 as $namespace => $dir )
                {
                    $this->loadPsr4( $dir, $namespace, $raw_class, $class );
                }
            }
            
            if( !EMPTY( $this->classmap ) )
            {
                if( ISSET( $this->classmap[ $raw_class ] ) )
                {
                    $dir        = $this->classmap[ $raw_class ];

                    $classmap   = array_search( $dir, $this->classmap );

                    $this->loadClassMap( $dir, $classmap, $raw_class );
                }
                
            }
 
        }
        
        if( $this->_cache ) 
        {
            if( !ISSET( $cache_data[ $class ] ) AND 
                ( in_array( $class, $this->_cache_class ) AND !in_array( $class, $except_class ) )
            ) 
            {
                $this->goCacheFile( $this->_cache_file_arr );         
            }

        }

    }

    /**
     * @param $path
     * @param $class_filename
     * @param $class
     */
    protected function loadPath( $path, $class_filename, $class )
    {

        if( is_dir( $path ) ) 
        {

            $dir                = static::recursiveDir( $path, TRUE );

            $arr                = static::recursiveIterator( $dir, TRUE );

            foreach( $arr as $file ) 
            {   
                $dir_filename   = $this->_check_file( $file->getFilename() );
                
                if ( $dir_filename == $class_filename ) 
                {

                    $full_path  = $file->getRealPath();                    

                    $filename   = $this->requireFile( $full_path );

                    $this->toCacheFile( $filename, $class, $full_path );

                    break;

                }

            }

        } 
        else 
        {   
            $dir_filename       = $this->_check_file( $this->_clean_class( $path, self::DS ) );

            if ( $dir_filename == $class_filename ) 
            {

                $filename                           = $this->requireFile( $path );

                $this->toCacheFile( $filename, $class, $path );

            }

        }
   
    }

    /**
     * @param $directories
     * @param $namespace
     * @param $class
     * @param $clean_class
     */
    protected function loadPsr4( $directories, $namespace, $class, $clean_class )
    {  

        if( is_string( $directories ) )
        {
            $directories    = array( $directories );
        }

        foreach( $directories as $directory )
        {
            if( strpos( $class, $namespace ) === 0  )
            {
                $full_path  = $this->processPsr4( $directory, $namespace, $class );
                
                $filename   = $this->requireFile( $full_path );

                $this->toCacheFile( $filename, $clean_class, $full_path );

            }

        }
      
    }

    /**
     * @param $directory
     * @param $namespace
     * @param $class
     * @return string
     */
    protected function processPsr4( $directory, $namespace, $class )
    {

        $directory          = $this->processDirectory( $directory );
        
        $file               = str_replace( '\\', '/', substr( $class, strlen( $namespace ) ) ) . $this->_file_ext;
        
        return $directory.$file;

    }


    /**
     * @param $directories
     * @param $classmap
     * @param $class
     */
    protected function loadClassMap( $directories, $classmap, $class )
    {

        if( is_string( $directories ) )
        {
            $directories    = array( $directories );
        }

        foreach ( $directories as $directory ) 
        {
            
            if( $classmap == $class )
            {

                $directory      = $this->processDirectory( $directory, TRUE );

                $class          = $this->_clean_class( $class );

                if( is_dir( $directory ) )
                {
                    $file       = $directory.self::DS.$class.$this->_file_ext;
                }
                else 
                {
                    $file       = $directory;
                }
                
                $filename       = $this->requireFile( $file );

                $this->toCacheFile( $filename, $class, $file );

            }

        }

    }

    /**
     * @param $file
     * @return bool
     */
    protected function requireFile( $file )
    {
        if( file_exists( $file ) )
        {
            $requiredFiles  = get_included_files();
            
            $search         = array_search($file, $requiredFiles);
            
            if( EMPTY( $search ) )
            {

                require $file;
            }

            return $file;
        }

        return FALSE;
    }

    /**
     * @param $directory
     * @param bool|FALSE $is_file
     * @return mixed|string
     */
    protected function processDirectory( $directory, $is_file = FALSE )
    {
        if( strpos( $directory, '/' ) === FALSE )
        {   
            $directory      = $directory.self::DS;
            
            $directory      = ( $directory == self::DS ) ? '' : $directory;
        }
        else 
        {
            $directory      = str_replace( '/', self::DS, $directory );
        }

        $directory          = ( !EMPTY( $this->_dir_root ) ) ? $this->_dir_root.self::DS.$directory : $directory;

        if( $is_file )
        {
            $directory      = rtrim( $directory, self::DS );
        }

        return $directory;
    }

    /**
     *
     */
    protected function loadFiles()
    {

        $from           = self::DS;
        $to             = $this->_file_ext;

        $cache_data     = $this->extractCacheData();
        $cache_data     = !EMPTY( $cache_data ) ? $cache_data : array();

        $cache          = TRUE;

        foreach( $this->files as $file )
        {

            $file       = $this->processDirectory( $file, TRUE );
            
            $from       = ( !EMPTY( $this->_dir_root ) ) ? $this->_dir_root.self::DS : $from;

            if( strpos( $file, self::DS ) === FALSE )
            {
                $sub    = $file;
            }
            else 
            {
                $sub    = substr( $file, strpos( $file, $from )+strlen( $from ), strlen( $file ) );
            }

            $orig_file  = substr( $sub, 0, strpos( $sub, $to ) );

            if( array_key_exists( $orig_file, $cache_data ) )
            {
                $identifier     = $cache_data[ $orig_file ];
                $cache          = FALSE;
            }
            else 
            {
                $salt           = ( round( microtime( TRUE ) *1000 ) + mt_rand( 1, mt_getrandmax() ) );
                $identifier     = hash( 'sha1', $orig_file . $salt );

            }
            
            if ( EMPTY( static::$cache_load_file[ $identifier ] ) ) 
            { 
                $filename       = $this->requireFile( $file );

                if( $filename !== FALSE )
                {
                    $cache_data[ $orig_file ]                   = $identifier;
                }

                static::$cache_load_file[ $identifier ]         = TRUE;
            }

        }
        
        if( $this->_cache AND $cache )
        {
            $this->goCacheFile( $cache_data );
        }

    }

    /**
     * @param $data
     */
    protected function goCacheFile( $data )
    {
        $serialized_paths       = serialize( $data );
                      
        file_put_contents( static::$_cache_file , NULL );
        file_put_contents( static::$_cache_file, $serialized_paths ); 
    }

    /**
     * @param $check
     * @param $class
     * @param $full_path
     */
    protected function toCacheFile( $check, $class, $full_path )
    {
        if( $check !== FALSE )
        {
            $this->_cache_file_arr[ $class ]    = $full_path;
            $this->_cache_class[]               = $class;
        }
    }

    /**
     * @param $file
     * @return mixed
     */
    private function _check_file( $file )
    {
        $file               = str_replace( $this->other_ext, '', $file );

        return $file;
    }

    /**
     * @param $class_name
     * @param string $delimiter
     * @return bool|string|void
     */
    private function _clean_class( $class_name, $delimiter = '\\' )
    {

        $class              = '';

        if( !EMPTY( $class_name ) ) 
        {

            $class          = trim( $class_name );

            if( (bool)strstr( $class, $delimiter ) ) 
            {

                $class      = substr( $class, strrpos( $class, $delimiter ) + 1 );

                $class      = trim( $class );

            }

        }

        return $class;

    }

    /**
     * @param $path_arr
     */
    private function _multi_path( $path_arr )
    {

        foreach( $path_arr as $key => $value ) 
        {

            $this->_path_arr[]      = $this->processDirectory( $value );

        }

    }

}

