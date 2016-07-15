<?php
defined('_JEXEC') or die('Restricted access');
/**
 * @package AZMailer
 * @author Adam Jakab
 * @license GNU/GPL
 **/
require_once 'AZMailerTableInfo.php';

/**
 * Class tbl_azmailer_category_item
 */
class tbl_azmailer_category_item extends AZMailerTableInfo {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->name = 'azmailer_category_item';
		$this->pk = 'id';
		$this->columns = $this->getColumns();
		$this->keys = array("category_id" => false);
		$this->data = $this->getData();
		$this->forceNewDataInsert = false;
		$this->forceNewDataInsert_checkColumn = "";
	}

	/**
	 * @return array
	 */
	private function getColumns() {
		$answer = array(
			array("Field" => "id", "Type" => "int(11) unsigned", "Null" => "NO", "Default" => "", "Extra" => "auto_increment"),
			array("Field" => "category_id", "Type" => "tinyint(3) unsigned", "Null" => "NO", "Default" => "", "Extra" => ""),
			array("Field" => "item_order", "Type" => "tinyint(3) unsigned", "Null" => "NO", "Default" => "1", "Extra" => ""),
			array("Field" => "is_default", "Type" => "tinyint(3) unsigned", "Null" => "NO", "Default" => "0", "Extra" => ""),
			array("Field" => "name", "Type" => "varchar(32)", "Null" => "NO", "Default" => "", "Extra" => "")
		);
		return ($answer);
	}

	/**
	 * @return array
	 */
	private function getData() {
		$answer = array(
			array(1, 1, 1, 'Unknown'),
			array(1, 2, 0, 'Client'),
			array(1, 3, 0, 'Supplier'),
			array(1, 4, 0, 'Contact'),
			array(1, 5, 0, 'Internal')
		);
		return ($answer);
	}
}
