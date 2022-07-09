<?php namespace AJD_validation\Contracts;

use AJD_validation\AJD_validation as v;
use AJD_validation\Contracts\Abstract_rule;

abstract class Abstract_search extends Abstract_rule
{
	public $haystack;
	public $identical;
	public $reverse;

	protected function validateEquals( $value )
	{
		if( $this->reverse )
		{
			if( is_array( $value ) )
			{
				return in_array( $this->haystack, $value );
			}

			if( $value === NULL OR $value === '' )
			{
				return ( $this->haystack == $value );  
			}
			
			$check 		= ( false !== mb_stripos( $value, $this->haystack, 0, mb_detect_encoding( $value ) ) );
		}
		else
		{
			if( is_array( $this->haystack ) )
			{
				return in_array( $value, $this->haystack );
			}

			if( $value === NULL OR $value === '' )
			{
				return ( $value == $this->haystack );  
			}

			$check 		= ( false !== mb_stripos( $this->haystack, $value, 0, mb_detect_encoding( $value ) ) );
		}

		return $check;
	}

	protected function validateIdentical( $value )
	{
		if( $this->reverse )
		{
			if( is_array( $value ) )
			{
				return in_array( $this->haystack, $value, TRUE );
			}

			if( $value === NULL OR $value === '' )
			{
				return ( $this->haystack === $value );  
			}

			$check 		= ( false !== mb_strpos( $value, $this->haystack, 0, mb_detect_encoding( $value ) ) );
		}
		else
		{
			if( is_array( $this->haystack ) )
			{
				return in_array( $value, $this->haystack, TRUE );
			}

			if( $value === NULL OR $value === '' )
			{
				return ( $value === $this->haystack );  
			}

			$check 		= ( false !== mb_strpos( $this->haystack, $value, 0, mb_detect_encoding( $value ) ) );
		}

		return $check;
	}

	public function run($value, $satisfier = NULL, $field = NULL)
	{
		if( is_array( $satisfier ) )
		{
			if( ISSET( $satisfier[0] ) )
			{
				if( EMPTY( $this->haystack ) )
				{
					$this->haystack 	= $satisfier[0];
				}
			}

			if( ISSET( $satisfier[1] ) AND is_bool( $satisfier[1] )  )
			{
				$this->identical 	= $satisfier[1];
			}
		}
		else
		{
			if( EMPTY( $this->haystack ) )
			{
				$this->haystack 		= $satisfier;
			}
		}
		
		$check_arr 	= array();
		$check 		= FALSE;

		if( $this->identical )
		{
			$check 	= $this->validateIdentical( $value );
		}
		else
		{
			$check 	= $this->validateEquals( $value );
		}

		return $check;
	}

	public function validate( $value )
	{
		$satisfier 	= array( $this->haystack, $this->identical );

		$check 		= $this->run( $value, $satisfier );

		if( is_array( $check ) )
		{
			return $check['check'];
		}
		
		return $check;
	}
}