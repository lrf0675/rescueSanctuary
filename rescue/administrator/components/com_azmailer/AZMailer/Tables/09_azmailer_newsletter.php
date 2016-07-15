<?php
defined('_JEXEC') or die('Restricted access');
/**
 * @package AZMailer
 * @author Adam Jakab
 * @license GNU/GPL
 **/
require_once 'AZMailerTableInfo.php';

/**
 * Class tbl_azmailer_newsletter
 */
class tbl_azmailer_newsletter extends AZMailerTableInfo {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->name = 'azmailer_newsletter';
		$this->pk = 'id';
		$this->columns = $this->getColumns();
		$this->keys = array("nl_create_date" => false, "nl_title" => false);
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
			array("Field" => "nl_create_date", "Type" => "int(11) unsigned", "Null" => "NO", "Default" => "0", "Extra" => ""),
			array("Field" => "nl_send_date", "Type" => "int(11) unsigned", "Null" => "NO", "Default" => "0", "Extra" => ""),
			array("Field" => "nl_title", "Type" => "varchar(255)", "Null" => "NO", "Default" => "", "Extra" => ""),
			array("Field" => "nl_title_internal", "Type" => "varchar(255)", "Null" => "NO", "Default" => "", "Extra" => ""),
			array("Field" => "nl_email_from", "Type" => "varchar(128)", "Null" => "NO", "Default" => "", "Extra" => ""),
			array("Field" => "nl_email_from_name", "Type" => "varchar(128)", "Null" => "NO", "Default" => "", "Extra" => ""),
			array("Field" => "nl_template_id", "Type" => "int(11) unsigned", "Null" => "NO", "Default" => "0", "Extra" => ""),
			array("Field" => "nl_textversion", "Type" => "mediumtext", "Null" => "YES", "Default" => "", "Extra" => ""),
			array("Field" => "nl_template_substitutions", "Type" => "mediumtext", "Null" => "YES", "Default" => "", "Extra" => ""),
			array("Field" => "nl_sendto_selections", "Type" => "text", "Null" => "YES", "Default" => "", "Extra" => ""),
			array("Field" => "nl_sendto_additional", "Type" => "mediumtext", "Null" => "YES", "Default" => "", "Extra" => ""),
			array("Field" => "nl_attachments", "Type" => "text", "Null" => "YES", "Default" => "", "Extra" => ""),
			array("Field" => "nl_selectcount", "Type" => "int(11) unsigned", "Null" => "NO", "Default" => "0", "Extra" => ""),
			array("Field" => "nl_sendcount", "Type" => "int(11) unsigned", "Null" => "NO", "Default" => "0", "Extra" => "")
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
