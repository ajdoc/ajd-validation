<?php namespace AJD_validation\Rules;

use finfo;
use SplFileInfo;
use AJD_Validation\Contracts\Abstract_rule;

class Mime_type_upload_rule extends Abstract_rule
{
	public $mimeTypes;
	private $fileInfo;
    protected $fileType         = '';
    protected $allowedTypes     = '';
    protected $inputFile;
    protected $tmpName;
    protected $ignoreMime;

	public function __construct($mimeTypes, $inputFile, $tmpName, $ignoreMime = FALSE, finfo $fileInfo = null)
    {
		$this->mimeTypes = $mimeTypes;
		$this->fileInfo  = $fileInfo ?: new finfo(FILEINFO_MIME_TYPE);
        $this->inputFile = $inputFile;
        $this->tmpName  = $tmpName;
        $this->ignoreMime = $ignoreMime;

        $this->setAllowedTypes($this->mimeTypes);
    }

    public function getExtension($filename, $file_ext_tolower = FALSE)
    {
        $x = explode('.', $filename);

        if (count($x) === 1)
        {
            return '';
        }

        $ext = ($file_ext_tolower) ? strtolower(end($x)) : end($x);
        return '.'.$ext;
    }


    /*if ( ! function_exists('function_usable'))
    {*/
        /**
         * Function usable
         *
         * Executes a function_exists() check, and if the Suhosin PHP
         * extension is loaded - checks whether the function that is
         * checked might be disabled in there as well.
         *
         * This is useful as function_exists() will return FALSE for
         * functions disabled via the *disable_functions* php.ini
         * setting, but not for *suhosin.executor.func.blacklist* and
         * *suhosin.executor.disable_eval*. These settings will just
         * terminate script execution if a disabled function is executed.
         *
         * The above described behavior turned out to be a bug in Suhosin,
         * but even though a fix was commited for 0.9.34 on 2012-02-12,
         * that version is yet to be released. This function will therefore
         * be just temporary, but would probably be kept for a few years.
         *
         * @link    http://www.hardened-php.net/suhosin/
         * @param   string  $function_name  Function to check for
         * @return  bool    TRUE if the function exists and is safe to call,
         *          FALSE otherwise.
         */
        public function function_usable($function_name)
        {
            static $_suhosin_func_blacklist;

            if (function_exists($function_name))
            {
                if ( ! isset($_suhosin_func_blacklist))
                {
                    if (extension_loaded('suhosin'))
                    {
                        $_suhosin_func_blacklist = explode(',', trim(ini_get('suhosin.executor.func.blacklist')));

                        if ( ! in_array('eval', $_suhosin_func_blacklist, TRUE) && ini_get('suhosin.executor.disable_eval'))
                        {
                            $_suhosin_func_blacklist[] = 'eval';
                        }
                    }
                    else
                    {
                        $_suhosin_func_blacklist = array();
                    }
                }

                return ! in_array($function_name, $_suhosin_func_blacklist, TRUE);
            }

            return FALSE;
        }
    // }

    public function setAllowedTypes($types)
    {
        $this->allowedTypes = (is_array($types) OR $types === '*')
            ? $types
            : explode('|', $types);
    }

    public function fileMimeType($file)
    {
        $regexp = '/^([a-z\-]+\/[a-z0-9\-\.\+]+)(;\s.+)?$/';

        if (function_exists('finfo_file'))
        {
            $finfo = @finfo_open(FILEINFO_MIME);
            if (is_resource($finfo)) 
            {
                $mime = @finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);

                if (is_string($mime) && preg_match($regexp, $mime, $matches))
                {
                    $this->fileType = $matches[1];
                    return;
                }
            }
        }

        if (DIRECTORY_SEPARATOR !== '\\')
        {
            $cmd = function_exists('escapeshellarg')
                ? 'file --brief --mime '.escapeshellarg($file['tmp_name']).' 2>&1'
                : 'file --brief --mime '.$file['tmp_name'].' 2>&1';

            if ($this->function_usable('exec'))
            {
                $mime = @exec($cmd, $mime, $return_status);
                if ($return_status === 0 && is_string($mime) && preg_match($regexp, $mime, $matches))
                {
                    $this->fileType = $matches[1];
                    return;
                }
            }

            if ( ! ini_get('safe_mode') && $this->function_usable('shell_exec'))
            {
                $mime = @shell_exec($cmd);
                if (strlen($mime) > 0)
                {
                    $mime = explode("\n", trim($mime));
                    if (preg_match($regexp, $mime[(count($mime) - 1)], $matches))
                    {
                        $this->fileType = $matches[1];
                        return;
                    }
                }
            }

            if ($this->function_usable('popen'))
            {
                $proc = @popen($cmd, 'r');
                if (is_resource($proc))
                {
                    $mime = @fread($proc, 512);
                    @pclose($proc);
                    if ($mime !== FALSE)
                    {
                        $mime = explode("\n", trim($mime));
                        if (preg_match($regexp, $mime[(count($mime) - 1)], $matches))
                        {
                            $this->fileType = $matches[1];
                            return;
                        }
                    }
                }
            }
        }

        if (function_exists('mime_content_type'))
        {
            $this->fileType = @mime_content_type($file['tmp_name']);
            if (strlen($this->fileType) > 0) 
            {
                return;
            }
        }

        $this->fileType = $file['type'];
    }

    public function run( $value, $satisfier = NULL, $field = NULL )
    {
    	$check 		= FALSE;

        $extension  = $this->getExtension($value);

        $this->fileMimeType($this->inputFile);
       
        if ($this->allowedTypes === '*')
        {
            // $check  = TRUE;
            return TRUE;
        }

        if( EMPTY($this->allowedTypes) OR !is_array($this->allowedTypes) )
        {
            // $check  = FALSE;
            return FALSE;
        }

        
        $ext = strtolower(ltrim($extension, '.'));
        
        if ( ! in_array($ext, array_keys( $this->allowedTypes ), TRUE))
        {
            // $check  = FALSE;
            return FALSE;
        }

        if (in_array($ext, array('gif', 'jpg', 'jpeg', 'jpe', 'png'), TRUE) && @getimagesize($this->tmpName) === FALSE)
        {

            // $check  = FALSE;
            return FALSE;
        }

        $mimes  = $this->mimeTypes;

        if (ISSET($mimes[$ext]))
        {

            return is_array($mimes[$ext])
                ? in_array($this->fileType, $mimes[$ext], TRUE)
                : ($mimes[$ext] === $this->fileType);
        }

        return FALSE;
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