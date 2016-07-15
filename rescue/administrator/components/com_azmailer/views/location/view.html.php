<?php
/**
 * @package    AZMailer
 * @author     Adam Jakab {@link http://www.alfazeta.com}
 * @author     Created on 09-Feb-2013
 * @license    GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
use AZMailer\Core\AZMailerException;
use AZMailer\Core\AZMailerView;
use AZMailer\Helpers\AZMailerAdminInterfaceHelper;

/**
 * Class AZMailerViewLocation
 */
class AZMailerViewLocation extends AZMailerView {

	/**
	 * @param null $tpl
	 * @return bool|mixed|void
	 * @throws AZMailerException
	 */
	function display($tpl = null) {
		$this->items = $this->get('Items');
		$this->filters = $this->get('Filters');
		$this->pagination = $this->get('Pagination');
		$this->state = $this->get('State');
		if (count($errors = $this->get('Errors'))) {
			throw new AZMailerException(implode('<br />', $errors));
		}
		//
		AZMailerAdminInterfaceHelper::setHeaderTitle(JText::_("COM_AZMAILER_TOOLBARTITLE_LOCATION"), "location");
		AZMailerAdminInterfaceHelper::addButtonsToToolBar(array(
			array("core.create", "location.new", 'new', 'JTOOLBAR_NEW', false),
		));
		return (parent::display($tpl));
	}

}
