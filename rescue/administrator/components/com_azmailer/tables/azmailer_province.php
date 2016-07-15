<?php
defined('_JEXEC') or die('Restricted access');

/**
 * @package AZMailer
 * @author Adam Jakab
 * @license GNU/GPL
 **/
class TableAzmailer_province extends JTable {
	var $id = null;
	var $region_id = null;
	var $province_name = null;
	var $province_sigla = null;

	/**
	 * @param \JDatabaseDriver $_db
	 */
	function __construct(&$_db) {
		parent::__construct('#__azmailer_province', 'id', $_db);
	}

	/**
	 * @return bool
	 */
	function check() {
		return true;
	}

}