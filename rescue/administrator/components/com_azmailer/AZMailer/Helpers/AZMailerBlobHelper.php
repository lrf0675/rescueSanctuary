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
 * Blob Helper Class
 *
 * @author jackisback
 */
class AZMailerBlobHelper {

	/**
	 * @param string $type
	 * @param integer $parentid
	 * @return mixed
	 */
	public static function getBlob($type = null, $parentid = 0) {
		$db = \JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.*');
		$query->from('#__azmailer_blob AS a');
		$query->where('a.parent_type = ' . $db->quote($type, true));
		$query->where('a.parent_id = ' . $db->quote($parentid, true));
		$db->setQuery($query);
		$tpl = $db->loadObject();
		return ($tpl);
	}


}