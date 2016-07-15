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
 * Class AZMailerViewStatistics
 */
class AZMailerViewStatistics extends AZMailerView {
	/**
	 * @param null $tpl
	 * @return mixed|void
	 */
	public function display($tpl = null) {
		parent::display();
		//
		AZMailerAdminInterfaceHelper::setHeaderTitle(\JText::_("COM_AZMAILER_SUBMENU_STATS"), "statistics");
		AZMailerAdminInterfaceHelper::addButtonsToToolBar(array(/*array("core.create", "subscriber.edit", 'new', 'JTOOLBAR_NEW', false),
			array("core.delete", "subscriber.delete", 'delete', 'JTOOLBAR_DELETE', true)*/
		));
	}
}
