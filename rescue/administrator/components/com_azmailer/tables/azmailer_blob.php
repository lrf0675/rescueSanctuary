<?php
/**
 * @package AZ Newsletter component for Joomla! 1.5
 * @author Adam Jakab
 * @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 **/
defined('_JEXEC') or die('Restricted access');

/**
 * Class TableAzmailer_blob
 */
class TableAzmailer_blob extends JTable {
	var $id = null;
	var $parent_id = null;
	var $parent_type = null;
	var $htmlblob = null;

	/**
	 * @param \JDatabaseDriver $_db
	 */
	function __construct(&$_db) {
		parent::__construct('#__azmailer_blob', 'id', $_db);
	}

	/**
	 * @return bool
	 */
	function check() {
		return true;
	}

}