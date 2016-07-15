<?php
defined('_JEXEC') or die('Restricted access');

/**
 * @package AZMailer
 * @author Adam Jakab
 * @license GNU/GPL
 **/
class TableAzmailer_newsletter extends JTable {
	var $id = null;
	var $nl_create_date = null;
	var $nl_send_date = null;
	var $nl_title = null;
	var $nl_title_internal = null;
	var $nl_email_from = null;
	var $nl_email_from_name = null;
	var $nl_template_id = null;
	var $nl_textversion = null;
	var $nl_template_substitutions = null;
	var $nl_sendto_selections = null;
	var $nl_sendto_additional = null;
	var $nl_attachments = null;
	var $nl_selectcount = null;
	var $nl_sendcount = null;

	/**
	 * @param \JDatabaseDriver $_db
	 */
	function __construct(&$_db) {
		parent::__construct('#__azmailer_newsletter', 'id', $_db);
	}

	/**
	 * @return bool
	 */
	function check() {
		return true;
	}

}