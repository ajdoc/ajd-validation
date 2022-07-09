<?php namespace AJD_validation\Contracts;

interface Uncompromised_interface 
{
	/**
     * Verify that the given data has not been compromised in data leaks.
     *
     * @param  array  $data
     * @return bool
     */
    public function verify($data);
}