<?php
defined('_JEXEC') or die('Restricted access');
/**
 * @package AZMailer
 * @author Adam Jakab
 * @license GNU/GPL
 **/
require_once 'AZMailerTableInfo.php';

/**
 * Class tbl_azmailer_newsletter_stat
 */
class tbl_azmailer_newsletter_stat extends AZMailerTableInfo {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->name = 'azmailer_newsletter_stat';
		$this->pk = 'id';
		$this->columns = $this->getColumns();
		$this->keys = array("stat_nl_id" => false, "stat_nls_mail" => false);
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
			array("Field" => "stat_type", "Type" => "varchar(16)", "Null" => "NO", "Default" => "", "Extra" => ""),
			array("Field" => "stat_nl_id", "Type" => "int(11) unsigned", "Null" => "NO", "Default" => "0", "Extra" => ""),
			array("Field" => "stat_date", "Type" => "int(11) unsigned", "Null" => "NO", "Default" => "0", "Extra" => ""),
			array("Field" => "stat_nls_mail", "Type" => "varchar(128)", "Null" => "NO", "Default" => "", "Extra" => ""),
			array("Field" => "stat_client", "Type" => "varchar(255)", "Null" => "YES", "Default" => "", "Extra" => "")
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
