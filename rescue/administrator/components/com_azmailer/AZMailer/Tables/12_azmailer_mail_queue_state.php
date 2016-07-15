<?php
defined('_JEXEC') or die('Restricted access');
/**
 * @package AZMailer
 * @author Adam Jakab
 * @license GNU/GPL
 **/
require_once 'AZMailerTableInfo.php';

/**
 * Class tbl_azmailer_mail_queue_state
 */
class tbl_azmailer_mail_queue_state extends AZMailerTableInfo {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->name = 'azmailer_mail_queue_state';
		$this->pk = 'id';
		$this->columns = $this->getColumns();
		$this->keys = array();
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
			array("Field" => "name", "Type" => "varchar(16)", "Null" => "NO", "Default" => "", "Extra" => ""),
			array("Field" => "last_updated_date", "Type" => "int(11) unsigned", "Null" => "NO", "Default" => "0", "Extra" => ""),
			array("Field" => "enabled", "Type" => "tinyint(5) unsigned", "Null" => "NO", "Default" => "0", "Extra" => ""),
			array("Field" => "blocked", "Type" => "tinyint(5) unsigned", "Null" => "NO", "Default" => "0", "Extra" => ""),
			array("Field" => "blocked_date", "Type" => "int(11) unsigned", "Null" => "NO", "Default" => "0", "Extra" => ""),
			array("Field" => "unsent_count", "Type" => "int(11) unsigned", "Null" => "NO", "Default" => "0", "Extra" => ""),
			array("Field" => "sent_count", "Type" => "int(11) unsigned", "Null" => "NO", "Default" => "0", "Extra" => "")
		);
		return ($answer);
	}

	/**
	 * @return array
	 */
	private function getData() {
		$answer = array(
			array('DEFAULT', 0, 1, 0, 0, 0, 0)
		);
		return ($answer);
	}
}
