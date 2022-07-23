<?php namespace AJD_validation\Helpers;

use AJD_validation\Contracts\Abstract_database;

final class Db_instance extends Abstract_database
{
	public function getDbInstance()
	{
		return $this->db;
	}
}