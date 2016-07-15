<?php
defined('_JEXEC') or die('Restricted access');
/**
 * @package AZMailer
 * @author Adam Jakab
 * @license GNU/GPL
 **/
require_once 'AZMailerTableInfo.php';

/**
 * Class tbl_azmailer_mail_queue_item
 */
class tbl_azmailer_mail_queue_item extends AZMailerTableInfo {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->name = 'azmailer_mail_queue_item';
		$this->pk = 'id';
		$this->columns = $this->getColumns();
		$this->keys = array("mq_date" => false,
			"mq_type" => false,
			"mq_typeid" => false,
			"mq_priority" => false,
			"mq_state" => false,
			"mq_subject" => false,
			"mq_from" => false,
			"mq_to" => false,
			"mq_send_attempt_count" => false,
			"mq_last_send_attempt_date" => false,
			"mq_last_send_attempt_result_code" => false,
			"mq_has_been_read" => false
		);
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
			array("Field" => "mq_date", "Type" => "int(11) unsigned", "Null" => "NO", "Default" => "0", "Extra" => ""),
			array("Field" => "mq_type", "Type" => "varchar(16)", "Null" => "NO", "Default" => "", "Extra" => ""),
			array("Field" => "mq_typeid", "Type" => "int(11) unsigned", "Null" => "NO", "Default" => "0", "Extra" => ""),
			array("Field" => "mq_priority", "Type" => "tinyint(3) unsigned", "Null" => "NO", "Default" => "1", "Extra" => ""),
			array("Field" => "mq_state", "Type" => "tinyint(3) unsigned", "Null" => "NO", "Default" => "0", "Extra" => ""),
			array("Field" => "mq_from", "Type" => "varchar(128)", "Null" => "NO", "Default" => "", "Extra" => ""),
			array("Field" => "mq_from_name", "Type" => "varchar(128)", "Null" => "NO", "Default" => "", "Extra" => ""),
			array("Field" => "mq_to", "Type" => "varchar(128)", "Null" => "NO", "Default" => "", "Extra" => ""),
			array("Field" => "mq_subject", "Type" => "varchar(255)", "Null" => "NO", "Default" => "", "Extra" => ""),
			array("Field" => "mq_body", "Type" => "mediumtext", "Null" => "NO", "Default" => "", "Extra" => ""),
			array("Field" => "mq_body_txt", "Type" => "mediumtext", "Null" => "NO", "Default" => "", "Extra" => ""),
			array("Field" => "mq_substitutions", "Type" => "text", "Null" => "NO", "Default" => "", "Extra" => ""),
			array("Field" => "mq_attachments", "Type" => "text", "Null" => "YES", "Default" => "", "Extra" => ""),
			array("Field" => "mq_send_attempt_count", "Type" => "tinyint(5) unsigned", "Null" => "NO", "Default" => "0", "Extra" => ""),
			array("Field" => "mq_last_send_attempt_date", "Type" => "int(11) unsigned", "Null" => "NO", "Default" => "0", "Extra" => ""),
			array("Field" => "mq_last_send_attempt_result_code", "Type" => "smallint(5) unsigned", "Null" => "NO", "Default" => "0", "Extra" => ""),
			array("Field" => "mq_last_send_attempt_log", "Type" => "text", "Null" => "YES", "Default" => "", "Extra" => ""),
			array("Field" => "mq_has_been_read", "Type" => "tinyint(3) unsigned", "Null" => "NO", "Default" => "0", "Extra" => "")
		);
		return ($answer);
	}

	/**
	 * @return array
	 */
	private function getData() {
		$answer = array();
		return ($answer);
	}
}
