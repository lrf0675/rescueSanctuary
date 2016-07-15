<?php
/**
 * @package    AZMailer
 * @author     Adam Jakab {@link http://www.alfazeta.com}
 * @author     Created on 09-Feb-2013
 * @license    GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
use AZMailer\Core\AZMailerView;
use AZMailer\Helpers\AZMailerAdminInterfaceHelper;

/**
 * Class AZMailerViewSettings
 */
class AZMailerViewSettings extends AZMailerView {

	/**
	 * @param null $tpl
	 * @return bool|mixed|void
	 */
	function display($tpl = null) {
		$this->items = array();
		$this->state = $this->get('State');
		//
		AZMailerAdminInterfaceHelper::setHeaderTitle(JText::_("COM_AZMAILER_TOOLBARTITLE_SETTINGS"), "paramconfig");
		AZMailerAdminInterfaceHelper::addButtonsToToolBar(array(
			array("core.manage", "settings.checkAndUpdateAZMailerTables", 'refresh', JText::_('COM_AZMAILER_TOOLBARBUTTON_DBSYNC')),
			//array("core.manage", "cp.display", 'back', "TestButton"),
		));
		//PREFERENCES BUTTON(com_config popup)
		$canDo = AZMailerAdminInterfaceHelper::getActions();
		if ($canDo->get('core.admin')) {
			\JToolBarHelper::preferences('com_azmailer');
		}
		return (parent::display($tpl));
	}

	/**
	 * @param null $tpl
	 */
	function checkAndUpdateAZMailerTables($tpl = null) {
		parent::display("raw");
		AZMailerAdminInterfaceHelper::setHeaderTitle(JText::_("COM_AZMAILER_TOOLBARTITLE_SETTINGS"), "paramconfig");
		AZMailerAdminInterfaceHelper::addButtonsToToolBar(array(
			array("core.manage", "settings.display", 'back', JText::_('COM_AZMAILER_TOOLBARBUTTON_BACK')),
		));
	}


}
