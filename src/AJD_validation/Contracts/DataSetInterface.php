<?php 

namespace AJD_validation\Contracts;

interface DataSetInterface 
{
	public function setName($name);

	public function addError($errors, $key = null, $rules_name = null, $field = null, $clean_field = null, $check_arr = false);

	public function appendError($errors);

	public function getName();

	public function field();

	public function rules();

	public function preValidate($value = null, $field = null, $check_arr = true);

	public function setPreValidate(array $preValidate);

	public function getPreValidate();

	public function setErrorMessage($errorMessage);

	public function getErrorMessage();

	public function setExceptionMessages(array $errorMessage);

	public function getExceptionMessages();

	public function validation($value = null, $key = null);

	/**
     * Dynamically set properties
     *
     *
     */
	public function __set($name, $value);

	/**
     * Dynamically get properties
     *
     *
     */
	public function __get($name);

	/**
     * Dynamically check if property is set
     *
     *
     */
	public function __isset($name);


}