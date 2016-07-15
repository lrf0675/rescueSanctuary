<?php
defined('_JEXEC') or die('Restricted access');

/**
 * @package AZMailer
 * @author Adam Jakab
 * @license GNU/GPL
 **/
class TableAzmailer_region extends JTable {
	var $id = null;
	var $country_id = null;
	var $region_name = null;
	var $region_sigla = null;

	/**
	 * @param \JDatabaseDriver $_db
	 */
	function __construct(&$_db) {
		parent::__construct('#__azmailer_region', 'id', $_db);
	}

	/**
	 * @return bool
	 */
	function check() {
		return true;
	}

}