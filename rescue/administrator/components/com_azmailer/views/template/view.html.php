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
 * Class AZMailerViewTemplate
 */
class AZMailerViewTemplate extends AZMailerView {

	/**
	 * @param null $tpl
	 * @return mixed|void
	 * @throws Exception
	 */
	function display($tpl = null) {
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state = $this->get('State');
		if (count($errors = $this->get('Errors'))) {
			throw new \Exception(implode('<br />', $errors), 500);
		}
		parent::display();
		//
		AZMailerAdminInterfaceHelper::setHeaderTitle(\JText::_("COM_AZMAILER_TOOLBARTITLE_TEMPLATE"), "template");
		AZMailerAdminInterfaceHelper::addButtonsToToolBar(array(
			array("core.create", "template.edit", 'new', 'JTOOLBAR_NEW', false),
			array("core.delete", "template.delete", 'delete', 'JTOOLBAR_DELETE', true)
		));
	}

	function edit() {
		/** @var AZMailerModelTemplate $model */
		$model = $this->getModel();
		/** @var $JI \JInput */
		$JI = \JFactory::getApplication()->input;
		$id = $JI->getInt("cid", 0);
		$this->item = $model->getSpecificItem($id);
		$this->state = $this->get('State');
		parent::display("edit");
		//
		AZMailerAdminInterfaceHelper::setHeaderTitle(\JText::_("COM_AZMAILER_TOOLBARTITLE_TEMPLATE"), "template");
		AZMailerAdminInterfaceHelper::addButtonsToToolBar(array(
			array("core.create", "template.apply", 'apply', 'JTOOLBAR_APPLY', false), /*save&stay*/
			array("core.create", "template.save", 'save', 'JTOOLBAR_SAVE', false), /*save&close*/
			array("core.create", "template.duplicate", 'save-copy', 'JTOOLBAR_SAVE_AS_COPY', false), /*save_as_copy*/
			array("core.manage", "template.display", 'cancel', 'JTOOLBAR_CANCEL', false), /*cancel*/
		));
		$JI->set("hidemainmenu", 1);//blocks main-menu
	}

	function apply() {
		$this->save(true);
	}

	/**
	 * @param bool $isApply
	 * @throws Exception
	 */
	function save($isApply = false) {
		global $AZMAILER;
		\JSession::checkToken() or jexit('Invalid Token');
		$model = $this->getModel();
		/** @var $JI \JInput */
		$JI = \JFactory::getApplication()->input;
		$model->saveSpecificItem($JI->getArray($_POST));
		if (!$isApply) {
			$AZMAILER->getController()->setRedirect(\JRoute::_('index.php?option=' . $AZMAILER->getOption("com_name") . '&task=template.display', false));
		} else {
			$newid = $model->getState($model->getName() . '.id');
			$AZMAILER->getController()->setRedirect(\JRoute::_('index.php?option=' . $AZMAILER->getOption("com_name") . '&task=template.edit&cid=' . $newid, false));
		}
	}

	function duplicate() {
		/** @var $JI \JInput */
		$JI = \JFactory::getApplication()->input;
		$JI->set("id", 0);
		$this->save(false);
	}

	function delete() {
		global $AZMAILER;
		$model = $this->getModel();
		/** @var $JI \JInput */
		$JI = \JFactory::getApplication()->input;
		$cid = $JI->get("cid", false, "array");
		$model->removeSpecificItems($cid);
		$AZMAILER->getController()->setRedirect(\JRoute::_('index.php?option=' . $AZMAILER->getOption("com_name") . '&task=template.display', false));
	}
}
