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
use JFactory;

/**
 * Template Helper Class
 *
 * @author jackisback
 */
class AZMailerTemplateHelper {

	/**
	 * @param string $tplCode
	 * @return int
	 */
	public static function getTemplateIdByCode($tplCode) {
		$db = \JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.id');
		$query->from('#__azmailer_template AS a');
		$query->where('a.tpl_code = ' . $db->quote($tplCode, true));
		$db->setQuery($query);
		$id = (int)$db->loadResult();
		return ($id);
	}

	/**
	 * @param integer $tplid
	 * @return mixed
	 */
	public static function getTemplateById($tplid) {
		$db = \JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.*');
		$query->from('#__azmailer_template AS a');
		$query->where('a.id = ' . $db->quote($tplid, true));
		$db->setQuery($query);
		$tpl = $db->loadObject();
		return ($tpl);
	}

	/**
	 * @param string $tplType
	 * @param bool $zeroOption
	 * @return array
	 */
	public static function getSelectOptions_TemplatesForType($tplType, $zeroOption = false) {
		$db = \JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.id, a.tpl_name as data');
		$query->from('#__azmailer_template AS a');
		$query->where('a.tpl_type = ' . $db->quote($tplType, true));
		$query->order('a.tpl_name');
		$db->setQuery($query);
		$lst = array();
		if ($zeroOption !== false) {
			$lst[] = \JHtml::_('select.option', '0', \JText::_($zeroOption), 'id', 'data');
		}
		$lst = array_merge($lst, $db->loadObjectList());
		return ($lst);
	}


}


/*
function getSelectOptions_TemplateTypes($zeroOption=false) {
        global $AZNL_CORE;
        $db = $AZNL_CORE->get('_jdb');
        $sql = 'SELECT DISTINCT tpl_type AS id, tpl_type AS data FROM #__aznl_template ORDER BY tpl_type';
        $db->setQuery($sql);
        $lst = array();
        if ($zeroOption!==false) {
            $lst[] = JHTML::_('select.option',  '0', JText::_($zeroOption), 'id', 'data' );
        }
        $lst = array_merge( $lst, $db->loadObjectList() );
        return ($lst);
    }

    function getTemplateManager() {
        if (is_null($this->templateManager)) {
            $this->templateManager = new AZNL_TemplateManager();
        }
        return($this->templateManager);
    }



 */
?>