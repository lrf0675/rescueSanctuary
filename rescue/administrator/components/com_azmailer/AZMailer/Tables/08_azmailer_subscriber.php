<?php
defined('_JEXEC') or die('Restricted access');
/**
 * @package AZMailer
 * @author Adam Jakab
 * @license GNU/GPL
 **/
require_once 'AZMailerTableInfo.php';

/**
 * Class tbl_azmailer_subscriber
 */
class tbl_azmailer_subscriber extends AZMailerTableInfo {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->name = 'azmailer_subscriber';
		$this->pk = 'id';
		$this->columns = $this->getColumns();
		$this->keys = array("nls_email" => true, "nls_firstname" => false, "nls_lastname" => false, "nls_country_id" => false, "nls_region_id" => false, "nls_province_id" => false, "nls_blacklisted" => false);
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
			array("Field" => "nls_email", "Type" => "varchar(128)", "Null" => "NO", "Default" => "", "Extra" => ""),
			array("Field" => "nls_firstname", "Type" => "varchar(64)", "Null" => "NO", "Default" => "", "Extra" => ""),
			array("Field" => "nls_lastname", "Type" => "varchar(64)", "Null" => "YES", "Default" => "", "Extra" => ""),
			array("Field" => "nls_subscribe_date", "Type" => "int(11) unsigned", "Null" => "NO", "Default" => "0", "Extra" => ""),
			array("Field" => "nls_ip", "Type" => "varchar(16)", "Null" => "YES", "Default" => "", "Extra" => ""),
			array("Field" => "nls_country_id", "Type" => "int(11) unsigned", "Null" => "NO", "Default" => "0", "Extra" => ""),
			array("Field" => "nls_region_id", "Type" => "int(11) unsigned", "Null" => "NO", "Default" => "0", "Extra" => ""),
			array("Field" => "nls_province_id", "Type" => "int(11) unsigned", "Null" => "NO", "Default" => "0", "Extra" => ""),
			array("Field" => "nls_cat_1", "Type" => "varchar(255)", "Null" => "YES", "Default" => "[]", "Extra" => ""),
			array("Field" => "nls_cat_2", "Type" => "varchar(255)", "Null" => "YES", "Default" => "[]", "Extra" => ""),
			array("Field" => "nls_cat_3", "Type" => "varchar(255)", "Null" => "YES", "Default" => "[]", "Extra" => ""),
			array("Field" => "nls_cat_4", "Type" => "varchar(255)", "Null" => "YES", "Default" => "[]", "Extra" => ""),
			array("Field" => "nls_cat_5", "Type" => "varchar(255)", "Null" => "YES", "Default" => "[]", "Extra" => ""),
			array("Field" => "nls_blacklisted", "Type" => "tinyint(3) unsigned", "Null" => "NO", "Default" => "0", "Extra" => ""),
			array("Field" => "nls_mail_validation_code", "Type" => "smallint(5) unsigned", "Null" => "NO", "Default" => "0", "Extra" => ""),
			array("Field" => "nls_mail_validation_log", "Type" => "text", "Null" => "YES", "Default" => "", "Extra" => "")
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
