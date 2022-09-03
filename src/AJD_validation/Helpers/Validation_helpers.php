<?php namespace AJD_validation\Helpers;

use AJD_validation\Helpers\Array_helper;
use AJD_validation\Contracts\Validator;

class Validation_helpers
{
	public static function initializeData($field, $masterData)
    {
        $data = Array_helper::dot(static::initializeFieldOnData($field, $masterData));

        return array_merge($data, static::extractValuesForWildcards(
            $masterData, $data, $field
        ));
    }

    public static function initializeProcessData( $field, $masterData )
    {
    	$newData = [];
    	$data = static::initializeData( $field, $masterData );
    	$validator = new Validator;
    	$pattern = str_replace('\*', '[^\.]*', preg_quote($field));

    	foreach( $data as $key => $value )
    	{
    		$startsWith = $validator->starts_with($key);

    		if( $startsWith->validate( $field ) OR (bool) preg_match('/^'.$pattern.'\z/', $key ) )
    		{
    			$newData[$key] = $value;
    		}
    	}

    	return $newData;

    }

    protected static function initializeFieldOnData($field, $masterData)
    {
    	$explicitPath = static::getLeadingExplicitFieldPath($field);
    	$data = static::extractDataFromPath($explicitPath, $masterData);
    	$validator = new Validator;

    	$paramValidator = $validator->one_or( Validator::contains('*'), Validator::ends_with('*') );

    	if( !$paramValidator->validate( $field ) )
    	{
    		return $data;
    	}

    	return Array_helper::dataSet( $data, $field, null, true );
    }

	protected static function extractValuesForWildcards($masterData, $data, $field)
	{
	    $keys = [];
	    $pattern = str_replace('\*', '[^\.]+', preg_quote($field));

	    foreach ($data as $key => $value) 
	    {
	        if ( (bool) preg_match( '/^'.$pattern.'/', $key, $matches ) ) 
	        {
	            $keys[] = $matches[0];
	        }
	    }

	    $keys = array_unique($keys);
	    $data = [];

	    foreach($keys as $key) 
	    {
	        $data[$key] = Array_helper::get($masterData, $key);
	    }

	    return $data;
	}

	public static function extractDataFromPath($field, $masterData)
    {
        $results = [];
        $value = Array_helper::get($masterData, $field, '__missing__');

        if($value !== '__missing__') 
        {
            Array_helper::set($results, $field, $value);
        }

        return $results;
    }

    public static function getLeadingExplicitFieldPath($field)
    {
        return ( rtrim( explode( '*', $field )[0], '.' ) ) ? : NULL;
    }

    public static function getParentPath($field)
    {
    	return ( trim( explode( '.', $field )[0] ) ) ? : NULL;
    }

    public static function removeParentPath($parentPath, $field)
    {
    	$fields = explode('.', $field);
    	$parentKey = array_search($parentPath, $fields);

    	if( isset( $fields[$parentKey] ) )
    	{
    		unset( $fields[$parentKey] );
    	}

    	return implode('.', $fields);
    }

    public static function formatAppendedError($messages, $exception = null, $clean_field = null, ...$args)
    {
        $firstMessage = str_replace('-', '', $messages[0]);
        $realMessage = array();
        $messages[0] = $firstMessage;
        $checkforMesage = 'Data validation failed for';
        $cnt = 0;

        foreach( $messages as $key => $message )
        {
            $origMessage = $message;

            if( preg_match('/'.$checkforMesage.'/', $message) )
            {
                $message = preg_replace('/Data validation failed for [\"]'.$clean_field.'[\"]/', '', $message );
                
                unset($messages[$key]);
            }

            if( $key != 0 )
            {
                $message = '<br/>&nbsp;&nbsp;'.$message;
            }
            else
            {
                $message = '<br/>&nbsp;&nbsp;&nbsp;-'.$message;
            }

            if( !preg_match('/'.$checkforMesage.'/', $origMessage) )
            {
                $realMessage[$cnt] = $message;

                $cnt++;
            }   
        }

        return rtrim(implode('', $realMessage), '.');
    }
}