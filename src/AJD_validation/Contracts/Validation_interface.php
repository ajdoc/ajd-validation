<?php 

namespace AJD_validation\Contracts;

interface Validation_interface 
{
	public function check($field, $value = null, $check_arr = true);
	public function checkAsync($field, $value = null, $function = null, $check_arr = true);

	public function checkDependent($field, $value = null, $origValue = null, array $customMessage = [], $check_arr = true);

	public function checkArr($field, $value, array $customMesage = [], $check_arr = true);

	public function checkGroup(array $data);

	public function middleware($name, $field, $value = null, $check_arr = true);

	public function checkAllMiddleware($field, $value = null, array $customMesage = [], $check_arr = true);

	
	
}