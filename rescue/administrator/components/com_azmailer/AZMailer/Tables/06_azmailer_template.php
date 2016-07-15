<?php
defined('_JEXEC') or die('Restricted access');
/**
 * @package AZMailer
 * @author Adam Jakab
 * @license GNU/GPL
 **/
require_once 'AZMailerTableInfo.php';

/**
 * Class tbl_azmailer_template
 */
class tbl_azmailer_template extends AZMailerTableInfo {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->name = 'azmailer_template';
		$this->pk = 'id';
		$this->columns = $this->getColumns();
		$this->keys = array("tpl_code" => true, "tpl_type" => false);
		$this->data = $this->getData();
		$this->forceNewDataInsert = true;
		$this->forceNewDataInsert_checkColumn = "tpl_code";

	}

	/**
	 * @return array
	 */
	private function getColumns() {
		$answer = array(
			array("Field" => "id", "Type" => "int(11) unsigned", "Null" => "NO", "Default" => "", "Extra" => "auto_increment"),
			array("Field" => "tpl_code", "Type" => "varchar(64)", "Null" => "NO", "Default" => "", "Extra" => ""),
			array("Field" => "tpl_type", "Type" => "varchar(32)", "Null" => "NO", "Default" => "", "Extra" => ""),
			array("Field" => "tpl_name", "Type" => "varchar(255)", "Null" => "NO", "Default" => "", "Extra" => ""),
			array("Field" => "tpl_title", "Type" => "varchar(255)", "Null" => "NO", "Default" => "", "Extra" => ""),
		);
		return ($answer);
	}

	/**
	 * @return array
	 */
	private function getData() {
		$answer = array(
			array('default', 'newsletter', 'Default Template', 'AZMailer Newsletter')
		);
		return ($answer);
	}
}
