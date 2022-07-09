<?php 

namespace AJD_validation\Contracts;

use Exception;
use AJD_validation\Contracts\Abstract_rule;

abstract class Abstract_compare extends Abstract_rule
{
	public $comparator;
	public $compareValue 	= "";
	public $toString;

	protected $validComparator 	= array(
		'==', '===', '!=', '!==', '<>', '<', '>', '<=', '>=', '<=>'
	);

	public function __construct($comparator, $compareValue = "", $toString = TRUE)
	{
		$validator 			= $this->getValidator();
		$validateComparator = $validator->required()->in($this->validComparator);

		$validComparatorStr = implode(', ', $this->validComparator);

		if( !$validateComparator->validate( $comparator ) )
		{
			throw new Exception('Comparator is required and must be either '.$validComparatorStr.'.');
		}

		$this->comparator 	= $comparator;
		$this->compareValue = $compareValue;
		$this->toString 	= $toString;
		
	}

	public function run( $value, $satisfier = NULL, $field = NULL )
	{
		$check 	= FALSE;

		$compareValue 		= $this->compareValue;

		if( EMPTY( $this->compareValue ) ) 
		{
			$compareValue 	= 'NULL';
		}

		if( $this->toString )
		{
			$check 	= eval(" return '".$value."' ".$this->comparator." '".$compareValue."'; ");
		}
		else
		{
			$check 	= eval(" return ".$value." ".$this->comparator." ".$compareValue."; ");
		}
		
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