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
use AZMailer\Entities\AZMailerQueueItem;

/**
 * Statistics Helper Class
 *
 * @author jackisback
 */
class AZMailerStatisticsHelper {

	/**
	 * @param integer $nlid
	 */
	public static function deleteStatisticsForNewsletter($nlid) {
		$db = \JFactory::getDbo();
		$sql = 'DELETE FROM #__azmailer_newsletter_stat WHERE stat_nl_id = ' . $nlid;
		$db->setQuery($sql);
		$db->execute();
	}

	/**
	 * DO NOT CALL THIS DIRECTLY!!! USE: AZMailerQueueItem->markMailQueueItemRead()
	 * @param AZMailerQueueItem $MQI
	 */
	public static function registerNewsletterStatistics($MQI) {
		if ($MQI->get("mq_type") == "newsletter") {
			$db = \JFactory::getDbo();
			$sql = 'SELECT COUNT(*) FROM #__azmailer_newsletter_stat AS res'
				. ' WHERE res.stat_nl_id = ' . $MQI->get("mq_typeid")
				. ' AND res.stat_nls_mail = ' . $db->quote($MQI->get("mq_to"));
			$db->setQuery($sql);
			$registerStatistics = ($db->loadResult() == 0);
		} else {
			//for any other type we don't care
			$registerStatistics = true;
		}

		if ($registerStatistics) {
			$data = array();
			$data["id"] = null;
			$data["stat_type"] = $MQI->get("mq_type");
			$data["stat_nl_id"] = $MQI->get("mq_typeid");//id of the newsletter
			$data["stat_date"] = AZMailerDateHelper::now();
			$data["stat_nls_mail"] = $MQI->get("mq_to");
			$data["stat_client"] = $_SERVER['HTTP_USER_AGENT'];
			$table = \JTable::getInstance('azmailer_newsletter_stat', 'Table');
			if ($table->bind($data)) {
				if ($table->check()) {
					if ($table->store()) {
						//OK
					}
				}
			}
		}
	}


}
