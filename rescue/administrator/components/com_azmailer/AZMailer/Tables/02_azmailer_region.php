<?php
defined('_JEXEC') or die('Restricted access');
/**
 * @package AZMailer
 * @author Adam Jakab
 * @license GNU/GPL
 **/
require_once 'AZMailerTableInfo.php';

/**
 * Class tbl_azmailer_region
 */
class tbl_azmailer_region extends AZMailerTableInfo {
	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
		$this->name = 'azmailer_region';
		$this->pk = 'id';
		$this->columns = $this->getColumns();
		$this->keys = array("country_id" => false);
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
			array("Field" => "country_id", "Type" => "int(11) unsigned", "Null" => "NO", "Default" => "", "Extra" => ""),
			array("Field" => "region_name", "Type" => "varchar(64)", "Null" => "NO", "Default" => "", "Extra" => ""),
			array("Field" => "region_sigla", "Type" => "varchar(8)", "Null" => "YES", "Default" => "", "Extra" => "")
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

	/*
	private function getDataOld() {
		$answer = array();

		//REGIONS FOR ITALY
		$sql = 'SELECT id FROM '.'#__'.'azmailer_country WHERE country_sigla = "IT"';
		$CID = $this->___loadSqlSingleResult($sql);
		$answer = array_merge($answer,
			$answer = array(
				array($CID, 'Abruzzo', 'ABR'),
				array($CID, 'Basilicata', 'BAS'),
				array($CID, 'Calabria', 'CAL'),
				array($CID, 'Campania', 'CAM'),
				array($CID, 'Emilia-Romagna', 'EMR'),
				array($CID, 'Friuli-Venezia Giulia', 'FVG'),
				array($CID, 'Lazio', 'LAZ'),
				array($CID, 'Liguria', 'LIG'),
				array($CID, 'Lombardia', 'LOM'),
				array($CID, 'Marche', 'MAR'),
				array($CID, 'Molise', 'MOL'),
				array($CID, 'Piemonte', 'PMT'),
				array($CID, 'Puglia', 'PUG'),
				array($CID, 'Sardegna', 'SRD'),
				array($CID, 'Sicilia', 'SIC'),
				array($CID, 'Toscana', 'TOS'),
				array($CID, 'Trentino-Alto Adige', 'TAA'),
				array($CID, 'Umbria', 'UMB'),
				array($CID, 'Valle d\'Aosta', 'VLA'),
				array($CID, 'Veneto', 'VEN')
			)
		);


		return ($answer);
	}
	*/
}
