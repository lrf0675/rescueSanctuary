<?php
/**
 * @package    AZMailer
 * @author     Adam Jakab {@link http://www.alfazeta.com}
 * @author     Created on 09-Feb-2013
 * @license    GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
use AZMailer\Core\AZMailerModel;
use AZMailer\Helpers\AZMailerLocationHelper;


/**
 * Category Model
 */
class AZMailerModelLocation extends AZMailerModel {
	/**
	 * @param array $config
	 */
	public function __construct($config = array()) {
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'a.id',
				'itemSigla', 'itemName'
			);
		}
		parent::__construct($config);
	}

	/**
	 * @return array
	 */
	public function getFilters() {
		$filters = array();
		$locationType = $this->getState('filter.location_type');
		//SELECTBOX-COUNTRY
		if ($locationType == "region" || $locationType == "province") {
			$filter_country = (int)$this->getState("filter.filter_country");
			$lst = AZMailerLocationHelper::getSelectOptions_Countries("COM_AZMAILER_FILTER_NONE");
			$filters['country'] = JHtml::_('select.genericlist', $lst, 'filter_country', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'id', 'data', $filter_country);
		}
		if ($locationType == "province") {
			$filter_country = (int)$this->getState("filter.filter_country");
			$filter_region = (int)$this->getState("filter.filter_region");
			$lst = AZMailerLocationHelper::getSelectOptions_Regions("COM_AZMAILER_FILTER_NONE", $filter_country);
			$filters['region'] = JHtml::_('select.genericlist', $lst, 'filter_region', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'id', 'data', $filter_region);
		}
		return ($filters);
	}

	/**
	 * @param null $add_what
	 * @param null $name
	 * @param null $sigla
	 * @param null $country_id
	 * @param null $region_id
	 * @return bool
	 */
	public function addNew($add_what = null, $name = null, $sigla = null, $country_id = null, $region_id = null) {
		$answer = false;
		if (!empty($add_what) && in_array($add_what, array("country", "region", "province"))) {
			if (!empty($name)) {
				$data = array();
				if ($add_what == "country") {
					$table = \JTable::getInstance('azmailer_country', 'Table');
					$data["country_name"] = $name;
					$data["country_sigla"] = $sigla;
				} else if ($add_what == "region") {
					$table = \JTable::getInstance('azmailer_region', 'Table');
					$data["country_id"] = $country_id;
					$data["region_name"] = $name;
					$data["region_sigla"] = $sigla;
				} else if ($add_what == "province") {
					$table = \JTable::getInstance('azmailer_province', 'Table');
					$data["region_id"] = $region_id;
					$data["province_name"] = $name;
					$data["province_sigla"] = $sigla;
				}
				if (isset($table)) {
					if ($table->save($data)) {
						$answer = true;
					} else {
						\JFactory::getApplication()->enqueueMessage("Error - Save failed: " . print_r($table->getErrors(), true));
					}
				} else {
					\JFactory::getApplication()->enqueueMessage("Error - Unknown Type ( $add_what )!");
				}
			} else {
				\JFactory::getApplication()->enqueueMessage('Error - Name is empty!');
			}
		} else {
			\JFactory::getApplication()->enqueueMessage("Error - Unknown Type ( $add_what )!");
		}
		return ($answer);
	}

	/**
	 * @param null $change_what
	 * @param null $name
	 * @param null $sigla
	 * @param null $id
	 * @return bool
	 */
	function changeName($change_what = null, $name = null, $sigla = null, $id = null) {
		$answer = false;
		if (!empty($change_what) && in_array($change_what, array("country", "region", "province"))) {
			if ($id != 0) {
				if (!empty($name)) {
					$data = array();
					$data["id"] = \JFactory::getApplication()->input->getInt('id', 0);
					$data[$change_what . "_name"] = $name;
					$data[$change_what . "_sigla"] = $sigla;
					$table = \JTable::getInstance('azmailer_' . $change_what, 'Table');
					if ($table->save($data)) {
						$answer = true;
					} else {
						\JFactory::getApplication()->enqueueMessage("Error - Save failed: " . print_r($table->getErrors(), true));
					}
				} else {
					\JFactory::getApplication()->enqueueMessage("Error - Name is empty!");
				}
			} else {
				\JFactory::getApplication()->enqueueMessage("Error - undefined object ID( $id )!");
			}
		} else {
			\JFactory::getApplication()->enqueueMessage("Error - Unknown Type ( $change_what )!");
		}
		return ($answer);
	}

	/**
	 * @param null $delete_what
	 * @param null $id
	 * @return bool|string
	 */
	function delete($delete_what = null, $id = null) {
		if (!empty($delete_what) && in_array($delete_what, array("country", "region", "province"))) {
			if ($id != 0) {
				if ($delete_what == "country") {
					$relCount = AZMailerLocationHelper::countRegionsInCountry($id);
				} else if ($delete_what == "region") {
					$relCount = AZMailerLocationHelper::countProvincesInRegion($id);
				} else if ($delete_what == "province") {
					$relCount = 0;
				}
				if (isset($relCount) && ($relCount) == 0) {
					$table = \JTable::getInstance('azmailer_' . $delete_what, 'Table');
					if ($table->delete($id)) {
						$answer = true;
					} else {
						\JFactory::getApplication()->enqueueMessage("Error - Delete failed: " . print_r($table->getErrors(), true));
						$answer = false;
					}
				} else {
					$answer = "Error - Impossible to delete - there are related objects!";
				}
			} else {
				$answer = "Error - undefined object ID( $id )!";
			}
		} else {
			$answer = "Error - Unknown Type ( $delete_what )!";
		}
		return ($answer);
	}

	/**
	 * @return JDatabaseQuery
	 */
	protected function getListQuery() {
		//$app = JFactory::getApplication('administrator');
		$location_type = $this->getState('filter.location_type', 'country');
		if ($location_type == "country") {
			return ($this->getListQueryCountry());
		} else if ($location_type == "region") {
			return ($this->getListQueryRegion());
		} else if ($location_type == "province") {
			return ($this->getListQueryProvince());
		} else {
			die("Location type is not set!");
		}
	}

	/**
	 * @return JDatabaseQuery
	 */
	private function getListQueryCountry() {
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select(
			$this->getState('list.select',
				'a.id, a.country_name AS itemName, a.country_sigla AS itemSigla, "" AS parentName'
			)
		);
		$query->from($db->quoteName('#__azmailer_country') . ' AS a');
		//ORDERING
		$orderCol = $this->state->get('list.ordering', 'id');
		$orderDirn = $this->state->get('list.direction', 'ASC');
		$query->order($db->escape($orderCol . ' ' . $orderDirn));
		//
		return $query;
	}

	/**
	 * @return JDatabaseQuery
	 */
	private function getListQueryRegion() {
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select(
			$this->getState('list.select',
				'a.id, a.region_name AS itemName, a.region_sigla AS itemSigla,'
				. ' b.country_name AS parentName'
			)
		);
		$query->from($db->quoteName('#__azmailer_region') . ' AS a');
		$query->join("INNER", "#__azmailer_country AS b ON b.id = a.country_id");
		//Filter by: COUNTRY
		if (($filter_country = (int)$this->getState('filter.filter_country'))) {
			$query->where('b.id = ' . $filter_country);
		}
		//ORDERING
		$orderCol = $this->state->get('list.ordering', 'id');
		$orderDirn = $this->state->get('list.direction', 'ASC');
		$query->order($db->escape($orderCol . ' ' . $orderDirn));
		//
		return $query;
	}

	/**
	 * @return JDatabaseQuery
	 */
	private function getListQueryProvince() {
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select(
			$this->getState('list.select',
				'a.id, a.province_name AS itemName, a.province_sigla AS itemSigla,'
				. ' CONCAT(c.country_name, " - " , b.region_name) AS parentName'
			)
		);
		$query->from($db->quoteName('#__azmailer_province') . ' AS a');
		$query->join("INNER", "#__azmailer_region AS b ON b.id = a.region_id");
		$query->join("INNER", "#__azmailer_country AS c ON c.id = b.country_id");
		//Filter by: COUNTRY
		if (($filter_country = (int)$this->getState('filter.filter_country'))) {
			$query->where('c.id = ' . $filter_country);
		}
		//Filter by: REGION
		if (($filter_region = (int)$this->getState('filter.filter_region'))) {
			$query->where('b.id = ' . $filter_region);
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
		//LOCATION TYPE
		$location_type = $this->getUserStateFromRequest($this->context . '.filter.location_type', 'filter_location_type', 'country', "STRING");
		$this->setState('filter.location_type', $location_type);

		//COUNTRY
		$filter_country = $this->getUserStateFromRequest($this->context . '.filter.filter_country', 'filter_country', 0, "INTEGER");
		$this->setState('filter.filter_country', $filter_country);

		//REGION
		$filter_region = $this->getUserStateFromRequest($this->context . '.filter.filter_region', 'filter_region', 0, "INTEGER");
		$this->setState('filter.filter_region', $filter_region);

		// Load the parameters.
		/**
		 * !!! from Joomla 3.3 \JRegistry does NOT exists anymore !!!
		 * It is mapped still to \Joomla\Registry\Registry but there is no class by that name
		 */
		$params = JComponentHelper::getParams('com_azmailer');
		$this->setState('params', $params);

		// List state information.
		parent::populateState($ordering, $direction);
	}


}