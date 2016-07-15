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
 * Class AZMailerViewCategory
 */
class AZMailerViewCategory extends AZMailerView {

	/**
	 * @param null $tpl
	 * @return mixed|void
	 * @throws AZMailerException
	 */
	function display($tpl = null) {
		$this->items = $this->get('Items');
		//$this->pagination = $this->get('Pagination');//--NO PAGINATION SINCE CATEGORIES WILL HAVE FEW ITEMS AND jQuery ORDERING
		$this->state = $this->get('State');
		if (count($errors = $this->get('Errors'))) {
			throw new AZMailerException(implode('<br />', $errors));
		}

		//
		AZMailerAdminInterfaceHelper::setHeaderTitle(JText::_("COM_AZMAILER_TOOLBARTITLE_CATEGORY"), "category");
		AZMailerAdminInterfaceHelper::addButtonsToToolBar(array(
			array("core.create", "category.new", 'new', 'JTOOLBAR_NEW', false),
		));
		return (parent::display($tpl));
	}

}
