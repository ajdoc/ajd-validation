<?php namespace AJD_validation\Rules;

use AJD_validation\Contracts\Abstract_rule;
use SplFileInfo;

class Dimensions_rule extends Abstract_rule
{
	public $options = [];

	public $width;
	public $height;
	public $maxHeight;
	public $maxWidth;
	public $minHeight;
	public $minWidth;
	public $ratio;

	protected $validator;

	public function __construct( array $options, $width, $height, $maxHeight = null, $maxWidth = null, $ratio = null )
	{
		$origOptions = $options;
		$options = $this->Fnumeric()
					->cacheFilter('width')
					->cacheFilter('height')
					->cacheFilter('maxHeight')
					->cacheFilter('maxWidth')
					->cacheFilter('minHeight')
					->cacheFilter('minWidth')
					->filterValues($options);

		$this->options = $options;

		if( isset( $options['width'] ) )
		{
			$this->width = $options['width'];
		}

		if( isset( $options['height'] ) )
		{
			$this->height = $options['height'];
		}

		if( isset( $options['maxHeight'] ) )
		{
			$this->maxHeight = $options['maxHeight'];
		}

		if( isset( $options['maxWidth'] ) )
		{
			$this->maxWidth = $options['maxWidth'];
		}

		if( isset( $options['minWidth'] ) )
		{
			$this->minWidth = $options['minWidth'];
		}

		if( isset( $options['minHeight'] ) )
		{
			$this->minHeight = $options['minHeight'];
		}
		
		if( isset( $origOptions['ratio'] ) )
		{
			$this->ratio = $origOptions['ratio'];
		}

		if( !empty( $options ) )
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

		$this->validator = $this->getValidator();
	}

	public function run( $value, $satisfier = null, $field = null )
	{
		$checkFileExistsAndImage = $this->validator
									->file()
									->file_exists()
									->image()
									->validate( $value );

		$check = false;

		$width = 0;
		$height = 0;

		if( $checkFileExistsAndImage )
		{
			if( $value instanceof \SplFileInfo )
			{
				$value = $value->getPathname();
			}

			if( !$sizeDetails = @getimagesize($value) )
			{
				$check = false;

				return $check;
			}

			list($width, $height) = $sizeDetails;
		}
		else
		{
			if( is_array( $value ) )
			{
				$width = ( isset( $value[0] ) ) ? $value[0] : 0;
				$height = ( isset( $value[1] ) ) ? $value[1] : 0;
			}
			else
			{
				$width = $value;
				$height = 0;
			}
		}

		$check = ( $this->dimensionBasicCheck( $width, $height ) || $this->ratioCheck( $width, $height ) );

		return $check;
	}

	public function validate( $value ) 
	{
		 $check = $this->run( $value );

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
			return false;
		}

		list($numerator, $denominator) = array_replace(
		    array(1, 1), array_filter(sscanf($this->ratio, '%f/%d'))
		);

		$precision = 1 / ( max($width, $height) + 1 );
		
		return abs($numerator / $denominator - $width / $height) > $precision;
	}

	protected function dimensionBasicCheck( $width, $height )
	{
		return ( !empty( $this->width ) && $width != $this->width ) || 
				( !empty( $this->minWidth ) && $this->minWidth > $width ) || 
				( !empty( $this->maxWidth ) && $this->maxWidth < $width ) || 
				( !empty( $this->height ) && $this->height != $height ) || 
				( !empty( $this->minHeight ) && $this->minHeight > $height ) || 
				( !empty( $this->maxHeight ) && $this->maxHeight < $height );
	}
}