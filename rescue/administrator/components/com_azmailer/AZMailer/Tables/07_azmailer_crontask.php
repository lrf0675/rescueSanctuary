<?php
defined('_JEXEC') or die('Restricted access');
/**
 * @package AZMailer
 * @author Adam Jakab
 * @license GNU/GPL
 **/
require_once 'AZMailerTableInfo.php';

/**
 * Class tbl_azmailer_crontask
 */
class tbl_azmailer_crontask extends AZMailerTableInfo {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->name = 'azmailer_crontask';
		$this->pk = 'id';
		$this->columns = $this->getColumns();
		$this->keys = array("enabled" => false, "lastExecutionTime" => false);
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
			array("Field" => "description", "Type" => "varchar(128)", "Null" => "NO", "Default" => "", "Extra" => ""),
			array("Field" => "executionIntervalSeconds", "Type" => "int(11) unsigned", "Null" => "NO", "Default" => "", "Extra" => ""),
			array("Field" => "cronTaskToCall", "Type" => "varchar(64)", "Null" => "NO", "Default" => "", "Extra" => ""),
			array("Field" => "enabled", "Type" => "tinyint(3) unsigned", "Null" => "NO", "Default" => "0", "Extra" => ""),
			array("Field" => "lastExecutionTime", "Type" => "int(11) unsigned", "Null" => "NO", "Default" => "0", "Extra" => "")
		);
		return ($answer);
	}

	/**
	 * @return array
	 */
	private function getData() {
		$answer = array(
			array('Execute Mail Queue', 180, 'mailQueueCheck', 1, 0)
		);
		return ($answer);
	}
}
