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
 * Editor Helper Class
 *
 * @author jackisback
 */
class AZMailerEditorHelper {

	/**
	 * @param string $ptype
	 * @param integer $pid
	 * @return integer
	 */
	public static function getBlobIdByParent($ptype = null, $pid = null) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.id');
		$query->from('#__azmailer_blob AS a');
		$query->where(array('a.parent_type = ' . $db->quote($ptype, true), 'a.parent_id = ' . $pid));
		$db->setQuery($query);
		$id = (int)$db->loadResult();
		return ($id);
	}


	/**
	 * returns url|link to editor
	 * @global \AZMailer\AZMailerCore $AZMAILER
	 * @param object  $ELP (title, parent_type, parent_id, return_uri[base64encoded])
	 * @param boolean $urlOnly
	 * @return string
	 */
	public static function getEditorLink($ELP, $urlOnly = false) {
		global $AZMAILER;
		$answer = '';
		$ELP->title = strip_tags($ELP->title);
		$lnk = 'index.php?option=' . $AZMAILER->getOption('com_name') . '&task=editor.edit&params=' . base64_encode(json_encode($ELP));
		if ($urlOnly == false) {
			$answer .= '<a href="' . $lnk . '" style="float:right">';
			$answer .= '<span class="ui-icon ui-icon-wrench" title="' . $ELP->title . '"></span>';
			$answer .= '</a>';
		} else {
			$answer = $lnk;
		}
		return ($answer);
	}
}
