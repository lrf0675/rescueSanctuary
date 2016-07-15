<?php
defined('_JEXEC') or die('Restricted access');

/**
 * @package AZMailer
 * @author Adam Jakab
 * @license GNU/GPL
 **/
class TableAzmailer_newsletter_stat extends JTable {
	var $id = null;
	var $stat_type = null;
	var $stat_nl_id = null;
	var $stat_date = null;
	var $stat_client = null;

	/**
	 * @param \JDatabaseDriver $_db
	 */
	function __construct(&$_db) {
		parent::__construct('#__azmailer_newsletter_stat', 'id', $_db);
	}

	/**
	 * @return bool
	 */
	function check() {
		return true;
	}

}