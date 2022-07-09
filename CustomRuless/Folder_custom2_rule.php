<?php 
namespace AJD_validation;

use AJD_validation\Contracts\Abstract_rule;
use AJD_validation\AJD_validation as v;

class Folder_custom2_rule extends Abstract_rule
{
	
	public function __construct() 
	{
	}

	public function run( $value, $satisfier = NULL, $field = NULL, $clean_field = NULL, $origValues = NULL )
	{
		$check 	= FALSE;

		try
		{
			$check = (strtolower($value) == 'folder_custom2');
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		catch( Exception $e )
		{
			throw $e;
		}

		return $check;

	}

	public function validate( $value )
	{
		$check 	= FALSE;

		try
		{
			$check 			= $this->run( $value );

			if( is_array( $check ) )
			{
				return $check['check'];
			}
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		catch( Exception $e )
		{
			throw $e;
		}

		return $check;
	}

}