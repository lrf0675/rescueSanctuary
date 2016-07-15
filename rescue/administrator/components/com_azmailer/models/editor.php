<?php
/**
 * @package    AZMailer
 * @author     Adam Jakab {@link http://www.alfazeta.com}
 * @author     Created on 09-Feb-2013
 * @license    GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
use AZMailer\Core\AZMailerModel;

/**
 * Template Model
 */
class AZMailerModelEditor extends AZMailerModel {

	/**
	 * @param array $config
	 */
	public function __construct($config = array()) {
		parent::__construct($config);
	}

	/**
	 * @param integer $id
	 * @return object
	 */
	public function getSpecificItem($id = null) {
		if(! ($item = $this->_getSpecificItem($id)) ) {
			$item = $this->getTable();
		}
		return ($item);
	}

	/**
	 * @param $data
	 * @return bool
	 */
	public function saveSpecificItem($data) {
		\JSession::checkToken() or jexit('Invalid Token');
		return ($this->_saveSpecificItem($data));
	}

	/**
	 * @param string  $type
	 * @param string  $prefix
	 * @param array $config
	 * @return JTable|mixed
	 */
	public function getTable($type = null, $prefix = null, $config = array()) {
		return JTable::getInstance(($type ? $type : 'azmailer_blob'), ($prefix ? $prefix : 'Table'), $config);
	}
}
