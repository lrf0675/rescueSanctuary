<?php
defined('_JEXEC') or die('Restricted access');

/**
 * @package AZMailer
 * @author Adam Jakab
 * @license GNU/GPL
 **/
class TableAzmailer_country extends JTable {
	var $id = null;
	var $country_name = null;
	var $country_sigla = null;

	/**
	 * @param \JDatabaseDriver $_db
	 */
	function __construct(&$_db) {
		parent::__construct('#__azmailer_country', 'id', $_db);
	}

	/**
	 * @return bool
	 */
	function check() {
		return true;
	}

}