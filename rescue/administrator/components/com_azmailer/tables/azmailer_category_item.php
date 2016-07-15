<?php
defined('_JEXEC') or die('Restricted access');

/**
 * @package AZMailer
 * @author Adam Jakab
 * @license GNU/GPL
 **/
class TableAzmailer_category_item extends JTable {
	var $id = null;
	var $category_id = null;
	var $item_order = null;
	var $is_default = null;
	var $name = null;

	/**
	 * @param \JDatabaseDriver $_db
	 */
	function __construct(&$_db) {
		parent::__construct('#__azmailer_category_item', 'id', $_db);
	}

	/**
	 * @return bool
	 */
	function check() {
		return true;
	}

}