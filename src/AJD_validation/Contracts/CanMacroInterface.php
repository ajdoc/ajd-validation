<?php 

namespace AJD_validation\Contracts;

interface CanMacroInterface
{
	/**
     * Returns an array of method name to be made as macro.
     *
     * @return array
     *
     */
	public function getMacros();
}