<?php
/**
 * @package AZMailer
 * @author Adam Jakab
 * @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 **/
defined('_JEXEC') or die('Restricted access');

/**
 * Class TableAzmailer_mail_queue_state
 */
class TableAzmailer_mail_queue_state extends JTable {
	var $id = null;
	var $name = null;
	var $last_updated_date = null;
	var $enabled = null;
	var $blocked = null;
	var $blocked_date = null;
	var $unsent_count = null;
	var $sent_count = null;

	/**
	 * @param \JDatabaseDriver $_db
	 */
	function __construct(&$_db) {
		parent::__construct('#__azmailer_mail_queue_state', 'id', $_db);
	}

	/**
	 * @return bool
	 */
	function check() {
		return true;
	}

}
