<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_rule;
use SplFileInfo;

class Dimensions_rule extends Abstract_rule
{
	public $options 	= array();

	public $width;
	public $height;
	public $maxHeight;
	public $maxWidth;
	public $minHeight;
	public $minWidth;
	public $ratio;

	protected $validator;

	public function __construct( array $options, $width, $height, $maxHeight = NULL, $maxWidth = NULL, $ratio = NULL )
	{
		$options 			= $this->Fnumeric()
									->cacheFilter('width')
									->cacheFilter('height')
									->cacheFilter('maxHeight')
									->cacheFilter('maxWidth')
									->cacheFilter('minHeight')
									->cacheFilter('minWidth')
									->filterValues($options);

		$this->options 		= $options;

		if( ISSET( $options['width'] ) )
		{
			$this->width 	= $options['width'];
		}

		if( ISSET( $options['height'] ) )
		{
			$this->height 	= $options['height'];
		}

		if( ISSET( $options['maxHeight'] ) )
		{
			$this->maxHeight = $options['maxHeight'];
		}

		if( ISSET( $options['maxWidth'] ) )
		{
			$this->maxWidth = $options['maxWidth'];
		}

		if( ISSET( $options['minWidth'] ) )
		{
			$this->minWidth = $options['minWidth'];
		}

		if( ISSET( $options['minHeight'] ) )
		{
			$this->minHeight = $options['minHeight'];
		}

		if( ISSET( $options['ratio'] ) )
		{
			$this->ratio 	= $options['ratio'];
		}

		if( !EMPTY( $options ) )
		{
			foreach( $options as $key => $value )
			{
				if( $key == 'ratio' )
				{
					continue;
				}

				if( !$this->getValidator()->numeric()->validate( $value ) )
				{
					throw new Exception($key.' must be numeric.');
				}
			}
		}

		$this->validator 	= $this->getValidator();
	}

	public function run( $value, $satisfier = NULL, $field = NULL )
	{
		$checkFileExistsAndImage 	= $this->validator
										->file()
										->file_exists()
										->image()
										->validate( $value );

		$check 						= FALSE;

		$width 						= 0;
		$height 					= 0;

		if( $checkFileExistsAndImage )
		{
			if( $value instanceof \SplFileInfo )
			{
				$value 				= $value->getPathname();
			}

			if( !$sizeDetails = @getimagesize($value) )
			{
				$check 				= FALSE;

				return $check;
			}

			list($width, $height) 	= $sizeDetails;
		}
		else
		{
			if( is_array( $value ) )
			{
				$width 				= ( ISSET( $value[0] ) ) ? $value[0] : 0;
				$height 			= ( ISSET( $value[1] ) ) ? $value[1] : 0;
			}
			else
			{
				$width 				= $value;
				$height 			= 0;
			}
		}

		$check 						= ( $this->dimensionBasicCheck( $width, $height ) OR $this->ratioCheck( $width, $height ) );

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

	protected function ratioCheck( $width, $height )
	{
		if( EMPTY( $this->ratio ) )
		{
			return FALSE;
		}

		list($numerator, $denominator) 	= array_replace(
		    array(1, 1), array_filter(sscanf($this->ratio, '%f/%d'))
		);

		$precision 						= 1 / max($width, $height);

		return abs($numerator / $denominator - $width / $height) > $precision;
	}

	protected function dimensionBasicCheck( $width, $height )
	{
		return ( !EMPTY( $this->width ) AND $width != $this->width ) OR 
				( !EMPTY( $this->minWidth ) AND $this->minWidth > $width ) OR 
				( !EMPTY( $this->maxWidth ) AND $this->maxWidth < $width ) OR 
				( !EMPTY( $this->height ) AND $this->height != $height ) OR 
				( !EMPTY( $this->minHeight ) AND $this->minHeight > $height ) OR 
				( !EMPTY( $this->maxHeight ) AND $this->maxHeight < $height );
	}
}