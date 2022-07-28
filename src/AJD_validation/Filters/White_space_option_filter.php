<?php namespace AJD_validation\Filters;

use AJD_validation\Contracts\Abstract_filter;

class White_space_option_filter extends Abstract_filter
{
	public function filter( $value, $satisfier = NULL, $field = NULL )
	{
		$filtValue 		= $value;

	 	if ( !EMPTY( $satisfier ) ) 
	 	{
            $satisfier_arr = str_split( $satisfier );
            
            $satisfier_imp = '';

            foreach( $satisfier_arr as $key => $sat )
            {
                if( !EMPTY( $sat ) )
                {
                    if( $key != 0 )
                    {
                        $satisfier_imp .= '|\\'.$sat;    
                    }
                    else
                    {
                        $satisfier_imp .= '\\'.$sat;
                    }      
                }
            }

            // $filtValue 	= str_replace( str_split( $satisfier ), '', $value );
            $filtValue      = preg_replace('/'.$satisfier_imp.'/', '', $value);
    	}
    	else
    	{
    		$filtValue 	= preg_replace('/\s/', '', $value);
    	}

        return $filtValue;
	}
}