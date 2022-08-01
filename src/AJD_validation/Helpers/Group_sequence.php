<?php 

namespace AJD_validation\Helpers;

use AJD_validation\Contracts\Grouping_sequence_interface;

class Group_sequence implements Grouping_sequence_interface
{
	private $sequence = [];

	public function __construct(array $sequence)
	{
		$this->sequence = $sequence;
	}

	public function sequence() : array
	{
		return $this->sequence;
	}
}