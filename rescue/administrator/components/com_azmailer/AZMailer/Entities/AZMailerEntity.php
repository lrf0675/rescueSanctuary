<?php
namespace AZMailer\Entities;
/**
 * @package    AZMailer
 * @subpackage Entities
 * @author     Adam Jakab {@link http://www.alfazeta.com}
 * @author     Created on 09-Feb-2013
 * @license    GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');

/**
 * Class AZMailerEntity
 * @package AZMailer\Entities
 */
class AZMailerEntity {
	protected $data;
	protected $options;

	/**
	 * @param \stdClass $entityData
	 * @param \stdClass $entityOptions
	 */
	function __construct($entityData = null, $entityOptions = null) {
		$this->setupEntityData($entityData);
		$this->setupEntityOptions($entityOptions);
	}

	/**
	 * @param \stdClass $entityData
	 */
	private function setupEntityData($entityData = null) {
		$this->data = new \stdClass();
		if ($entityData) {
			foreach ($entityData as $k => $v) {
				$this->data->$k = $v;
			}
		}
	}

	/**
	 * @param \stdClass $entityOptions
	 */
	private function setupEntityOptions($entityOptions = null) {
		$this->options = new \stdClass();
		if ($entityOptions) {
			foreach ($entityOptions as $k => $v) {
				$this->options->$k = $v;
			}
		}
	}

	/**
	 * Low level getter function
	 * @param string $key
	 * @return mixed (requested data or false if no key is set)
	 */
	public function get($key = null) {
		if (isset($this->data->$key)) {
			return ($this->data->$key);
		}
		return (false);
	}

	/**
	 * @param string $key
	 * @param mixed  $val
	 */
	public function set($key = null, $val = null) {
		if ($key) {
			$this->data->$key = $val;
		}
	}

	/**
	 * @return array
	 */
	protected function getDataArray() {
		$answer = array();
		foreach ($this->data as $k => $v) {
			$answer[$k] = $v;
		}
		return ($answer);
	}
}