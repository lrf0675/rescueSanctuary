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
 * Queue Manager Helper Class
 *
 * @author jackisback
 */
class AZMailerQueuemanagerHelper {

	/**
	 * @param integer $id
	 * @return mixed
	 */
	public static function getMQIById($id) {
		$db = \JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.*');
		$query->from('#__azmailer_mail_queue_item AS a');
		$query->where('a.id = ' . $db->quote($id));
		$db->setQuery($query);
		return ($db->loadObject());
	}

	/**
	 * If queue has not been executed for some time there will be stale count on unsent mails
	 * @param bool $forceRecalculateUnsentMails
	 * @return mixed
	 */
	public static function getMailQueueState($forceRecalculateUnsentMails = false) {
		$db = \JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.*');
		$query->from('#__azmailer_mail_queue_state AS a');
		$query->where('a.id = 1');
		$db->setQuery($query);
		$answer = $db->loadObject();
		if ($forceRecalculateUnsentMails) {
			$answer->unsent_count = self::countUnsentMails();
		}
		return ($answer);
	}

	/**
	 * @return integer
	 */
	public static function countUnsentMails() {
		$db = \JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(*)');
		$query->from('#__azmailer_mail_queue_item AS a');
		$query->where('a.mq_state = 0');
		$db->setQuery($query);
		return ((int)$db->loadResult());
	}

	//todo: should this method be called 'setMailQueueState'??
	/**
	 * @param integer $state
	 * @return bool
	 */
	public static function setMailQueue($state) {
		$state = ($state == 1 ? $state : 0);
		/** @var \JTable $MQS */
		$MQS = \JTable::getInstance('azmailer_mail_queue_state', 'Table');
		$MQS->load(1);
		$data["id"] = 1;
		$data["enabled"] = $state;
		return ($MQS->save($data));
	}

	/**
	 * @param string|bool $zeroOption
	 * @return array
	 */
	public static function getSelectOptions_Type($zeroOption = false) {
		$db = \JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('DISTINCT a.mq_type AS id, a.mq_type AS data');
		$query->from('#__azmailer_mail_queue_item AS a');
		$query->order("a.mq_type ASC");
		$db->setQuery($query);
		$lst = array();
		if ($zeroOption !== false) {
			$lst[] = \JHtml::_('select.option', '0', \JText::_($zeroOption), 'id', 'data');
		}
		$lst = array_merge($lst, $db->loadObjectList());
		return ($lst);
	}

	/**
	 * @param string|bool $zeroOption
	 * @return array
	 */
	public static function getSelectOptions_Priority($zeroOption = false) {
		$lst = array();
		if ($zeroOption !== false) {
			$lst[] = \JHTML::_('select.option', '999', \JText::_($zeroOption), 'id', 'data');
		}
		$lst[] = \JHTML::_('select.option', '0', '0', 'id', 'data');
		$lst[] = \JHTML::_('select.option', '1', '1', 'id', 'data');
		$lst[] = \JHTML::_('select.option', '2', '2', 'id', 'data');
		$lst[] = \JHTML::_('select.option', '3', '3', 'id', 'data');
		$lst[] = \JHTML::_('select.option', '4', '4', 'id', 'data');
		$lst[] = \JHTML::_('select.option', '5', '5', 'id', 'data');
		return ($lst);
	}

	/**
	 * @param string|bool $zeroOption
	 * @return array
	 */
	public static function getSelectOptions_State($zeroOption = false) {
		$lst = array();
		if ($zeroOption !== false) {
			$lst[] = \JHTML::_('select.option', '999', \JText::_($zeroOption), 'id', 'data');
		}
		$lst[] = \JHTML::_('select.option', '1', \JText::_("COM_AZMAILER_MQM_TIT_MQI_STATE_SENT"), 'id', 'data');
		$lst[] = \JHTML::_('select.option', '0', \JText::_("COM_AZMAILER_MQM_TIT_MQI_STATE_UNSENT"), 'id', 'data');
		$lst[] = \JHTML::_('select.option', '2', \JText::_("COM_AZMAILER_MQM_TIT_MQI_STATE_FAILED"), 'id', 'data');
		return ($lst);
	}


}

/*

    function markMailQueueItemRead($mqiid) {
        $answer = false;
        $table  = \JTable::getInstance('aznl_mail_queue_items', 'Table');
        $data = array();
        $data["id"] = $mqiid;
        $data["mq_has_been_read"] = 1;
        if ($table->bind( $data )) {
            if ($table->check()) {
                if ($table->store()) {
                    $answer = true;
                }
            }
        }
        return($answer);
    }
 *
 * //----------------------------------------------------------------------- MAIL QUEUE ITEMS
	function countMQIbyState($nlid, $state=0) {
		global $AZNL_CORE;
		$db = $AZNL_CORE->get('_jdb');
		$sql =  'SELECT COUNT(*) FROM #__aznl_mail_queue_items AS res'
				.' WHERE res.mq_typeid = ' . $nlid
				.' AND res.mq_type = ' . $db->quote("newsletter")
				.' AND res.mq_state = ' . $state
				.'';
		$db->setQuery($sql);
		return($db->loadResult());
	}

 */
?>