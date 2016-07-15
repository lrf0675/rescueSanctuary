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
 * Queuemanager Model - for queue items
 */
class AZMailerModelQueuemanager extends AZMailerModel {

	/**
	 * @param array $config
	 */
	public function __construct($config = array()) {
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'a.id',
				'mq_date', 'a.mq_date',
				'mq_type', 'a.mq_type',
				'mq_priority', 'a.mq_priority',
				'mq_from', 'a.mq_from',
				'mq_to', 'a.mq_to',
				'mq_subject', 'a.mq_subject',
				'mq_send_attempt_count', 'a.mq_send_attempt_count',
				'mq_last_send_attempt_date', 'a.mq_last_send_attempt_date'
			);
		}
		parent::__construct($config);
	}

	/**
	 * @param array $cidArray
	 * @return bool
	 */
	public function removeSpecificItems($cidArray) {
		$delcnt = 0;
		$table = $this->getTable();
		while (count($cidArray)) {
			$cid = array_pop($cidArray);
			$table->load($cid);
			if ($table->delete($cid)) {
				$delcnt++;
			} else {
				\JFactory::getApplication()->enqueueMessage("Delete error on table " . $table->name);
			}
		}
		return (true);
	}

	/**
	 * @param string  $type
	 * @param string  $prefix
	 * @param array $config
	 * @return JTable|mixed
	 */
	public function getTable($type = null, $prefix = null, $config = array()) {
		return JTable::getInstance(($type ? $type : 'azmailer_mail_queue_item'), ($prefix ? $prefix : 'Table'), $config);
	}

	/**
	 * @return JDatabaseQuery
	 */
	protected function getListQuery() {
		$db = \JFactory::getDBO();
		$query = $db->getQuery(true);
		//
		// Select the required fields from the table.
		$query->select(
			$this->getState('list.select',
				'a.*'
			)
		);
		$query->from($db->quoteName('#__azmailer_mail_queue_item') . ' AS a');

		//Search
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			$search = $db->quote('%' . $db->escape($search, true) . '%');
			$query->where('(a.mq_from LIKE ' . $search . ' OR a.mq_from_name LIKE ' . $search . ' OR a.mq_to LIKE ' . $search . ' OR a.mq_subject LIKE ' . $search . ')');
		}

		//TYPE FILTER
		$type = $this->getState('filter.type_sel');
		if ($type != "0") {
			$type = $db->quote($db->escape($type, true));
			$query->where('a.mq_type = ' . $type);
		}

		//PRIORITY FILTER
		$priority = $this->getState('filter.priority_sel');
		if ($priority != 999) {
			$query->where('a.mq_priority = ' . $priority);
		}

		//PRIORITY FILTER
		$state = $this->getState('filter.state_sel');
		if ($state != 999) {
			$query->where('a.mq_state = ' . $state);
		}

		//ORDERING
		$orderCol = $this->state->get('list.ordering', 'mq_date');
		$orderDirn = $this->state->get('list.direction', 'DESC');
		$query->order($db->escape($orderCol . ' ' . $orderDirn));
		//echo $query;
		return $query;
	}

	/**
	 * @param string $ordering
	 * @param string $direction
	 */
	protected function populateState($ordering = "mq_date", $direction = "DESC") {
		//Filters
		$this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', "STRING"));

		//SELECTORS
		$this->setState('filter.type_sel', $this->getUserStateFromRequest($this->context . '.filter.type_sel', 'filter_type_sel', "0", "STRING"));
		$this->setState('filter.priority_sel', $this->getUserStateFromRequest($this->context . '.filter.priority_sel', 'filter_priority_sel', 999, "INT"));
		$this->setState('filter.state_sel', $this->getUserStateFromRequest($this->context . '.filter.state_sel', 'filter_state_sel', 999, "INT"));


		//Component parameters
		$params = \JComponentHelper::getParams('com_azmailer');
		$this->setState('params', $params);
		//
		parent::populateState($ordering, $direction);
	}
}