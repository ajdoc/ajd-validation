<?php namespace CustomLogics;

use AJD_validation\Contracts\Abstract_logic;


class Db_example_logic extends Abstract_logic
{
	protected $mainDb;
	public function __construct($mainDb = null, $validator = null)
	{
		if(!empty($mainDb) && $this->checkDbInstance($mainDb))
		{
			$this->mainDb = $mainDb;
		}
	}

	public function logic( $value )
	{
		$db = null;

		if(!empty($this->mainDb))
		{
			if($this->mainDb  )
			$db = $this->mainDb;
		}
		else 
		{
			if(isset($this->db))
			{
				$db = $this->db;
			}
		}

		if(!empty($db))
		{
			$query = "
				SELECT 	a.*
				FROM 	requests a
				WHERE 	a.request_id = ?
			";
			
			$result = $db->rawQuery($query, [$value]);

			return (!empty($result));
		}
		else
		{
			return false;	
		}
	}
}