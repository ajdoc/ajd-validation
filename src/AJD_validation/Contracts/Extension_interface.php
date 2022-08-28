<?php namespace AJD_validation\Contracts;

interface Extension_interface 
{
	/**
     * Returns an array of method name to be made ajd rules.
     *
     * @return array
     *
     */
	public function getRules();

	/**
     * Returns an array of rule method name and error message key value pair.
     *
     * @return array
     *
     */
	public function getRuleMessages();

	/**
     * Run the extension's defined rules and returns a bool.
     *
     * @return bool
     *
     */
	public function runRules( $rule, $value, $satisfer, $field );

	/**
     * Return unique name of the extension
     *
     * @return string
     *
     */
	public function getName();

	/**
     * Returns an array of method name to be made as an ajd middlewares.
     *
     * @return array
     *
     */
	public function getMiddleWares();

	/**
     * Returns an array of method name to be made as an ajd filters.
     *
     * @return array
     *
     */
	public function getFilters();

	/**
     * Returns an array of method name to be made as an ajd logics.
     *
     * @return array
     *
     */
	public function getLogics();

	/**
     * Returns an array of anonymous classes to be made as an ajd rule.
     *
     * @return array
     *
     */
	public function getAnonClass();

	/**
     * Returns an array of method name to be made as a macro.
     *
     * @return array
     *
     */
	public function getMacros();

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