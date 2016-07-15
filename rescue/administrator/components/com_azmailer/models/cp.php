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
 * ControlPanel Model
 */
class AZMailerModelCp extends AZMailerModel {

	/**
	 * Return component information
	 * @return array
	 */
	public function getCpInfo() {
		global $AZMAILER;
		//
		$xmlData = $AZMAILER->getInstallXmlData();
		//
		$answer = array(
			array("key" => JText::_('COM_AZMAILER_CP_COMPNAME'), "value" => $xmlData["name"]),
			array("key" => JText::_('COM_AZMAILER_CP_VERSION'), "value" => $xmlData["version"]),
			array("key" => JText::_('COM_AZMAILER_CP_RELDATE'), "value" => $xmlData["creationDate"]),
			array("key" => JText::_('COM_AZMAILER_CP_AUTHOR'), "value" => '<a href="http://devshed.jakabadambalazs.com" target="_blank">' . $xmlData["author"] . '</a>'),
			array("key" => JText::_('COM_AZMAILER_CP_SUPPORT'), "value" => '<a href="' . $xmlData["authorUrl"] . '" target="_blank">' . $xmlData["authorUrl"] . '</a>'),
			array("key" => JText::_('COM_AZMAILER_CP_LICENSE'), "value" => 'GNU/GPL2'),
		);
		return ($answer);
	}

	/**
	 * Buttons for CP
	 * @return array
	 */
	public function getCpButtons() {
		global $AZMAILER;
		$answer = array();
		//
		$linkbase = 'index.php?option=' . $AZMAILER->getOption('com_name') . '&';
		$iconpos = $AZMAILER->getOption("com_uri_admin") . "/assets/images/48x48/";
		//
		$answer[] = array(
			"link" => $linkbase . 'task=newsletter.display',
			"icon" => "${iconpos}newsletter.png",
			"title" => JText::_('COM_AZMAILER_SUBMENU_NEWSLETTER')
		);

		$answer[] = array(
			"link" => $linkbase . 'task=subscriber.display',
			"icon" => "${iconpos}addressbook.png",
			"title" => JText::_('COM_AZMAILER_SUBMENU_NLSUBSCRIBER')
		);

		$answer[] = array(
			"link" => $linkbase . 'task=queuemanager.display',
			"icon" => "${iconpos}queue.png",
			"title" => JText::_('COM_AZMAILER_SUBMENU_MAILQUEUEMANAGER')
		);

		$answer[] = array(
			"link" => $linkbase . 'task=category.display',
			"icon" => "${iconpos}categories.png",
			"title" => JText::_('COM_AZMAILER_SUBMENU_CATEGORY')
		);

		$answer[] = array(
			"link" => $linkbase . 'task=template.display',
			"icon" => "${iconpos}puzzle.png",
			"title" => JText::_('COM_AZMAILER_SUBMENU_TEMPLATE')
		);

		$answer[] = array(
			"link" => $linkbase . 'task=location.display',
			"icon" => "${iconpos}world.png",
			"title" => JText::_('COM_AZMAILER_SUBMENU_LOCATION')
		);

		$answer[] = array(
			"link" => $linkbase . 'task=statistics.display',
			"icon" => "${iconpos}stats.png",
			"title" => JText::_('COM_AZMAILER_SUBMENU_STATS')
		);

		$answer[] = array(
			"link" => $linkbase . 'task=settings.display',
			"icon" => "${iconpos}config.png",
			"title" => JText::_('COM_AZMAILER_SUBMENU_SETTINGS')
		);
		return ($answer);
	}
}

