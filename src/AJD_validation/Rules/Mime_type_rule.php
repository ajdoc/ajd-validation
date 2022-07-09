<?php namespace AJD_validation\Rules;

use finfo;
use SplFileInfo;
use AJD_Validation\Contracts\Abstract_rule;

class Mime_type_rule extends Abstract_rule
{
	public $mimetype;
	private $fileInfo;

	public function __construct($mimetype, finfo $fileInfo = null)
    {
		$this->mimetype = $mimetype;
		$this->fileInfo = $fileInfo ?: new finfo(FILEINFO_MIME_TYPE);
    }

    public function run( $value, $satisfier = NULL, $field = NULL )
    {
    	$check 		= FALSE;

    	if( $value instanceof SplFileInfo )
    	{
    		$value 	= $value->getPathname();
    	}

    	if( !is_string( $value ) )
    	{
    		$check 	= FALSE;
    	}

    	if( !is_file( $value ) )  
    	{
    		$check 	= FALSE;
    	}

    	$check 		= ( $this->fileInfo->file( $value ) == $this->mimetype );

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