<?php
/**
 * @package    AZMailer
 * @author     Adam Jakab {@link http://www.alfazeta.com}
 * @author     Created on 09-Feb-2013
 * @license    GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
use AZMailer\Core\AZMailerModel;
use AZMailer\Helpers\AZMailerNewsletterHelper;
use AZMailer\Helpers\AZMailerTemplateHelper;

/**
 * Template Model
 */
class AZMailerModelTemplate extends AZMailerModel {

	/**
	 * @param array $config
	 */
	public function __construct($config = array()) {
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'a.id', 'a.tpl_name', 'a.tpl_code', 'a.tpl_type', 'a.tpl_title'
			);
		}
		parent::__construct($config);
	}

	/**
	 * @param integer $id
	 * @return mixed
	 */
	public function getSpecificItem($id = null) {
		if(! ($item = $this->_getSpecificItem($id)) ) {
			$item = $this->getTable();
		}
		return ($item);
	}

	/**
	 * @param array $data
	 * @return bool
	 */
	public function saveSpecificItem($data) {
		\JSession::checkToken() or jexit('Invalid Token');
		$data["tpl_code"] = str_replace(" ", "_", $data["tpl_code"]);
		//check for duplicated template code
		$codeVerified = false;
		while (!$codeVerified) {
			$tplCodeHolderId = AZMailerTemplateHelper::getTemplateIdByCode($data["tpl_code"]);
			if ($tplCodeHolderId == 0 || $tplCodeHolderId == $data["id"]) {
				$codeVerified = true;
			} else {
				$data["tpl_code"] = '_' . $data["tpl_code"];
			}
		}
		return($this->_saveSpecificItem($data));
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
			if ($table->tpl_code != "default") {
				if (AZMailerNewsletterHelper::countNewslettersWithTemplateId($table->id) == 0) {
					if ($table->delete($cid)) {
						$delcnt++;
					} else {
						\JFactory::getApplication()->enqueueMessage("Error while deleting from table " . $table->name);
					}
				} else {
					\JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_AZMAILER_TEMPLATE_ERR_DELETE_USED', $table->tpl_code));
				}
			} else {
				\JFactory::getApplication()->enqueueMessage(JText::_('COM_AZMAILER_TEMPLATE_ERR_DELETE_ISDEFAULT'));
			}
		}
		return(true);
	}

	/**
	 * @param string $type
	 * @param string  $prefix
	 * @param array $config
	 * @return JTable|mixed
	 */
	public function getTable($type = null, $prefix = null, $config = array()) {
		return JTable::getInstance(($type ? $type : 'azmailer_template'), ($prefix ? $prefix : 'Table'), $config);
	}

	/**
	 * @return JDatabaseQuery
	 */
	protected function getListQuery() {
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		//
		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'b.htmlblob, a.*'
			)
		);
		$query->from($db->quoteName('#__azmailer_template') . ' AS a');
		$query->leftJoin('#__azmailer_blob AS b ON (b.parent_type="template" AND b.parent_id = a.id)');

		//Search
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			$search = $db->Quote('%' . $db->escape($search, true) . '%');
			$query->where('(a.tpl_name LIKE ' . $search . ' OR a.tpl_code LIKE ' . $search . ' OR a.tpl_title LIKE ' . $search . ')');
		}

		//ORDERING
		$orderCol = $this->state->get('list.ordering', 'id');
		$orderDirn = $this->state->get('list.direction', 'ASC');
		$query->order($db->escape($orderCol . ' ' . $orderDirn));
		//
		return $query;
	}

	/**
	 * Method to auto-populate the model state.
	 * Note. Calling getState in this method will result in recursion.
	 * @param string $ordering
	 * @param string $direction
	 */
	protected function populateState($ordering = "id", $direction = "ASC") {
		//Filters
		$this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', "STRING"));

		//Component parameters
		$params = JComponentHelper::getParams('com_azmailer');
		$this->setState('params', $params);

		//
		parent::populateState($ordering, $direction);
	}
}

