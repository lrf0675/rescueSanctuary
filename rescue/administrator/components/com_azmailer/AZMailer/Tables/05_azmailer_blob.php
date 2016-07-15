<?php
defined('_JEXEC') or die('Restricted access');
/**
 * @package AZMailer
 * @author Adam Jakab
 * @license GNU/GPL
 **/
require_once 'AZMailerTableInfo.php';

/**
 * Class tbl_azmailer_blob
 */
class tbl_azmailer_blob extends AZMailerTableInfo {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->name = 'azmailer_blob';
		$this->pk = 'id';
		$this->columns = $this->getColumns();
		$this->keys = array("parent_id" => false, "parent_type" => false);
		$this->data = $this->getData();
		$this->forceNewDataInsert = false;
		$this->forceNewDataInsert_checkColumn = "";
	}

	/**
	 * @return array
	 */
	private function getColumns() {
		return (array(
			array("Field" => "id", "Type" => "int(11) unsigned", "Null" => "NO", "Default" => "", "Extra" => "auto_increment"),
			array("Field" => "parent_id", "Type" => "int(11) unsigned", "Null" => "NO", "Default" => "", "Extra" => ""),
			array("Field" => "parent_type", "Type" => "varchar(16)", "Null" => "NO", "Default" => "", "Extra" => ""),
			array("Field" => "htmlblob", "Type" => "mediumtext", "Null" => "YES", "Default" => "", "Extra" => "")
		));
	}

	/**
	 * @return array
	 */
	private function getData() {
		$tplContent = file_get_contents(__DIR__ . DS . 'deftpl.html');
		$answer = array(array(1, 'template', $tplContent));
		return ($answer);
	}
}
