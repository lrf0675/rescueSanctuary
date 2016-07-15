<?php
/**
 * @package AZ Newsletter component for Joomla! 1.5
 * @author Adam Jakab
 * @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 **/
defined('_JEXEC') or die('Restricted access');

/**
 * Class TableAzmailer_subscriber
 */
class TableAzmailer_subscriber extends JTable {
	var $id = null;
	var $nls_email = null;
	var $nls_firstname = null;
	var $nls_lastname = null;
	var $nls_subscribe_date = null;
	var $nls_ip = null;
	//
	var $nls_country_id = null;
	var $nls_region_id = null;
	var $nls_province_id = null;
	//
	var $nls_cat_1 = null;
	var $nls_cat_2 = null;
	var $nls_cat_3 = null;
	var $nls_cat_4 = null;
	var $nls_cat_5 = null;
	//
	var $nls_blacklisted = null;
	var $nls_mail_validation_code = null;
	var $nls_mail_validation_log = null;

	/**
	 * @param \JDatabaseDriver $_db
	 */
	function __construct(&$_db) {
		parent::__construct('#__azmailer_subscriber', 'id', $_db);
	}

	/**
	 * @return bool
	 */
	function check() {
		return true;
	}

}