<?php
defined('_JEXEC') or die('Restricted access');

/**
 * @package AZMailer
 * @author Adam Jakab
 * @license GNU/GPL
 **/
class AZMailerTableInfo {
	/** @var string Table name without prefix */
	public $name = null;
	/** @var string primary key column name */
	public $pk = null;
	/** @var array columns(arrays)[with keys as defined by "SHOW COLUMNS"]->array("Field"=>"id", "Type"=>"int(11)", "Null"=>"NO", "Default"=>"NULL", "Extra"=>"auto_increment") */
	public $columns = array();
	/** @var array INDEX KEYS (unique) -> array("col1"=>true, "col2"=>false) */
	public $keys = array();
	/** @var array data(arrays)[as defined by column without the id field] */
	public $data = array();
	/** @var boolean primary key column name */
	public $forceNewDataInsert = false;
	/** @var string column name to use for check when forceDataInsert is enabled */
	public $forceNewDataInsert_checkColumn = null;
	/** @var boolean will be set to true if table was created affresh */
	public $isNewTable = false;

	//
	private $db;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->db = \JFactory::getDBO();
	}

	/**
	 * @param string|\JDatabaseQuery $sql - The SQL statement to set either as a JDatabaseQuery object or a string.
	 * @return bool|mixed
	 */
	protected function ___loadSqlSingleResult($sql) {
		try {
			$this->db->setQuery($sql);
			$res = $this->db->loadResult();
			return ($res);
		} catch (\RuntimeException $e) {
			return (false);
		}
	}


}
