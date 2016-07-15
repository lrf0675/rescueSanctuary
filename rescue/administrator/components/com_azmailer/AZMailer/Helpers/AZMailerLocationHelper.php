<?php
namespace AZMailer\Helpers;
/**
 * @package    AZMailer
 * @subpackage Helpers
 * @author     Adam Jakab {@link http://www.alfazeta.com}
 * @author     Created on 09-Feb-2013
 * @license    GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');


/**
 * Location Helper Class
 *
 * @author jackisback
 */
class AZMailerLocationHelper {

	//----------------------------------------------------------------------------------------------COUNTRIES
	/**
	 * @param bool|string $zeroOption
	 * @return array
	 */
	public static function getSelectOptions_Countries($zeroOption = false) {
		$db = \JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.id, a.country_name AS data');
		$query->from('#__azmailer_country AS a');
		$query->order("a.country_name ASC");
		$db->setQuery($query);
		$lst = array();
		if ($zeroOption !== false) {
			$lst[] = \JHtml::_('select.option', '0', \JText::_($zeroOption), 'id', 'data');
		}
		$lst = array_merge($lst, $db->loadObjectList());
		return ($lst);
	}

	/**
	 * For Xls Importer
	 * @param string $name
	 * @param bool   $registerIfNew
	 * @return bool|int
	 */
	public static function getCountryIdByName($name, $registerIfNew = false) {
		$answer = false;
		if (!empty($name)) {
			$db = \JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('a.id');
			$query->from('#__azmailer_country AS a');
			$query->where('LOWER(a.country_name) = ' . $db->quote(strtolower($name)));
			$db->setQuery($query);
			$answer = $db->loadResult();
			if (!$answer || empty($answer)) {
				$answer = false;
			}
			if (!$answer && $registerIfNew) {
				$data = array();
				$data["id"] = null;
				$data["country_name"] = ucfirst(strtolower($name));
				$data["country_sigla"] = strtoupper(substr($name, 0, 2));
				/** @var \JTable $table */
				$table = \JTable::getInstance('azmailer_country', 'Table');
				if ($table->bind($data)) {
					if ($table->check()) {
						if ($table->store()) {
							$db = $table->getDBO();
							$answer = $db->insertid();
						}
					}
				}
			}
		}
		return ($answer);
	}


	//-----------------------------------------------------------------------REGIONS
	/**
	 * @param string|bool $zeroOption
	 * @param int  $country_id
	 * @return array
	 */
	public static function getSelectOptions_Regions($zeroOption = false, $country_id = 0) {
		$db = \JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.id, a.region_name AS data');
		$query->from('#__azmailer_region AS a');
		$query->where('a.country_id = ' . $country_id);
		$query->order("a.region_name ASC");
		$db->setQuery($query);
		$lst = array();
		if ($zeroOption !== false) {
			$lst[] = \JHTML::_('select.option', '0', \JText::_($zeroOption), 'id', 'data');
		}
		$lst = array_merge($lst, $db->loadObjectList());
		return ($lst);
	}

	/**
	 * @param int $country_id
	 * @return int
	 */
	public static function countRegionsInCountry($country_id = 0) {
		$db = \JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(*)');
		$query->from('#__azmailer_region AS a');
		$query->where('a.country_id = ' . $country_id);
		$db->setQuery($query);
		return ((int)$db->loadResult());
	}

	/**
	 * For Xls Importer
	 * @param string $name
	 * @param int    $country_id
	 * @param bool   $registerIfNew
	 * @return bool|int
	 */
	public static function getRegionIdByName($name, $country_id, $registerIfNew = false) {
		$answer = false;
		if (!empty($name)) {
			$db = \JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('a.id');
			$query->from('#__azmailer_region AS a');
			$query->where('a.country_id = ' . $db->quote($country_id));
			$query->where('LOWER(a.region_name) = ' . $db->quote(strtolower($name)));
			$db->setQuery($query);
			$answer = $db->loadResult();
			if (!$answer || empty($answer)) {
				$answer = false;
			}
			if (!$answer && $registerIfNew) {
				$data = array();
				$data["id"] = null;
				$data["country_id"] = $country_id;
				$data["region_name"] = ucfirst(strtolower($name));
				$data["region_sigla"] = "";
				/** @var \JTable $table */
				$table = \JTable::getInstance('azmailer_region', 'Table');
				if ($table->bind($data)) {
					if ($table->check()) {
						if ($table->store()) {
							$db = $table->getDBO();
							$answer = $db->insertid();
						}
					}
				}
			}
		}
		return ($answer);
	}


	//-------------------------------------------------------------------PROVINCES
	/**
	 * @param string|bool $zeroOption
	 * @param int  $region_id
	 * @return array
	 */
	public static function getSelectOptions_Provinces($zeroOption = false, $region_id = 0) {
		$db = \JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.id, a.province_name AS data');
		$query->from('#__azmailer_province AS a');
		$query->where('a.region_id = ' . $region_id);
		$query->order("a.province_name ASC");
		$db->setQuery($query);
		$lst = array();
		if ($zeroOption !== false) {
			$lst[] = \JHTML::_('select.option', '0', \JText::_($zeroOption), 'id', 'data');
		}
		$lst = array_merge($lst, $db->loadObjectList());
		return ($lst);
	}

	/**
	 * @param int $region_id
	 * @return int
	 */
	public static function countProvincesInRegion($region_id = 0) {
		$db = \JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(*)');
		$query->from('#__azmailer_province AS a');
		$query->where('a.region_id = ' . $region_id);
		$db->setQuery($query);
		return ((int)$db->loadResult());
	}

	/**
	 * For Xls Importer
	 * @param string $name
	 * @param int    $region_id
	 * @param bool   $registerIfNew
	 * @return bool|int
	 */
	public static function getProvinceIdByName($name, $region_id, $registerIfNew = false) {
		$answer = false;
		if (!empty($name)) {
			$db = \JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('a.id');
			$query->from('#__azmailer_province AS a');
			$query->where('a.region_id = ' . $db->quote($region_id));
			$query->where('LOWER(a.province_name) = ' . $db->quote(strtolower($name)));
			$db->setQuery($query);
			$answer = $db->loadResult();
			if (!$answer || empty($answer)) {
				$answer = false;
			}
			if (!$answer && $registerIfNew) {
				$data = array();
				$data["id"] = null;
				$data["region_id"] = $region_id;
				$data["province_name"] = ucfirst(strtolower($name));
				$data["province_sigla"] = "";
				/** @var \JTable $table */
				$table = \JTable::getInstance('azmailer_province', 'Table');
				if ($table->bind($data)) {
					if ($table->check()) {
						if ($table->store()) {
							$db = $table->getDBO();
							$answer = $db->insertid();
						}
					}
				}
			}
		}
		return ($answer);
	}


	//---------------------------------------------------------------------------------------------ALL
	/**
	 * @param int $cid
	 * @param int $rid
	 * @param int $pid
	 * @return array
	 */
	public static function getCountryRegionProvince($cid = 0, $rid = 0, $pid = 0) {
		$answer = array();
		if ($cid) {
			$db = \JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('a.*');
			$query->from('#__azmailer_country AS a');
			$query->where('a.id = ' . $cid);
			if ($rid) {
				$query->select('b.*');
				$query->join('INNER', '#__azmailer_region AS b ON b.country_id = a.id');
				$query->where('b.id = ' . $rid);
			}
			if ($pid) {
				$query->select('c.*');
				$query->join('INNER', '#__azmailer_province AS c ON c.region_id = b.id');
				$query->where('c.id = ' . $pid);
			}
			$db->setQuery($query);
			$res = $db->loadObject();
			if ($res) {
				if ($cid && isset($res->country_name)) {
					$answer['country'] = new \stdClass();
					$answer['country']->id = $cid;
					$answer['country']->country_name = $res->country_name;
					$answer['country']->country_sigla = $res->country_sigla;
				}
				if ($rid && isset($res->region_name)) {
					$answer['region'] = new \stdClass();
					$answer['region']->id = $rid;
					$answer['region']->region_name = $res->region_name;
					$answer['region']->region_sigla = $res->region_sigla;
				}
				if ($pid && isset($res->province_name)) {
					$answer['province'] = new \stdClass();
					$answer['province']->id = $pid;
					$answer['province']->province_name = $res->province_name;
					$answer['province']->province_sigla = $res->province_sigla;
				}
			}
		}
		return ($answer);
	}

}


/*
 function getSelectOptions_LOCATION_TYPES($zeroOption=false) {
        global $AZNL_CORE;
        $lst = array();
        if($zeroOption !== false) {
            $lst[] = JHTML::_('select.option', '0', JText::_($zeroOption), 'id', 'data' );
        }
        $lst[] = JHTML::_('select.option',  'country', "Paese", 'id', 'data' );
        $lst[] = JHTML::_('select.option',  'region', "Regione", 'id', 'data' );
        $lst[] = JHTML::_('select.option',  'province', "Provincia", 'id', 'data' );
        return ($lst);
    }
 *

 */
?>