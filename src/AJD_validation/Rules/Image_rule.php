<?php namespace AJD_validation\Rules;

use finfo;
use SplFileInfo;
use AJD_validation\Contracts\Abstract_rule;

class Image_rule extends Abstract_rule
{
	public $fileInfo;

	public function __construct( finfo $fileInfo = NULL )
	{
		$this->fileInfo 	= $fileInfo ?: new finfo( FILEINFO_MIME_TYPE );
	}

	public function run( $value, $satisfier = NULL, $field = NULL )
	{
		$check 			= FALSE;

		if( $value instanceof SplFileInfo )
		{
			$value 		= $value->getPathname();
		}

		if( !is_string( $value ) )
		{
			$check 		= FALSE;
		}

		if( !is_file( $value ) )
		{
			$check 		= FALSE;
		}

		$check 			= ( 0 === @strpos( $this->fileInfo->file( $value ), 'image/' ) );
		
		return $check;
	}

	public function validate( $value )
	{
		 $check              = $this->run( $value );

        if( is_array( $check ) )
        {
            return $check['check'];
        }

        return $check;
	}
}