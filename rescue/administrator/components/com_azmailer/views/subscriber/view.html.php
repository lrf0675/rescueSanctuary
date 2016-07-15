<?php
/**
 * @package    AZMailer
 * @author     Adam Jakab {@link http://www.alfazeta.com}
 * @author     Created on 09-Feb-2013
 * @license    GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
use AZMailer\Core\AZMailerView;
use AZMailer\Entities\AZMailerSubscriber;
use AZMailer\Helpers\AZMailerAdminInterfaceHelper;

/**
 * Class AZMailerViewSubscriber
 */
class AZMailerViewSubscriber extends AZMailerView {
	protected $items;
	protected $item;

	/**
	 * @param null $tpl
	 * @return mixed|void
	 * @throws Exception
	 */
	public function display($tpl = null) {
		$this->items = $this->get('Items');
		foreach ($this->items as &$item) {
			$item = new AZMailerSubscriber($item);
		}
		$totalRecords = count($this->items);//$this->get("TotalRecords");
		$this->filters = $this->get('Filters');
		$this->pagination = $this->get('Pagination');
		$this->state = $this->get('State');
		//
		if (count($errors = $this->get('Errors'))) {
			throw new \Exception(implode('<br />', $errors), 500);
		}
		//
		parent::display();
		//
		AZMailerAdminInterfaceHelper::setHeaderTitle(\JText::_("COM_AZMAILER_SUBMENU_NLSUBSCRIBER") . "($totalRecords)", "subscribers");
		AZMailerAdminInterfaceHelper::addButtonsToToolBar(array(
			array("core.create", "subscriber.edit", 'new', 'JTOOLBAR_NEW', false),
			array("core.create", "subscriber.import", 'import', 'COM_AZMAILER_TOOLBARBUTTON_IMPORT', false),
			array("core.delete", "subscriber.delete", 'delete', 'JTOOLBAR_DELETE', true)
		));
	}

	/**
	 * Edit
	 */
	public function edit() {
		/** @var AZMailerModelSubscriber $model */
		$model = $this->getModel();
		/** @var $JI \JInput */
		$JI = \JFactory::getApplication()->input;
		$id = $JI->getInt("cid", 0);
		$this->item = new AZMailerSubscriber($model->getSpecificItem($id));
		$this->state = $this->get('State');
		parent::display("edit");
		//
		AZMailerAdminInterfaceHelper::setHeaderTitle(\JText::_("COM_AZMAILER_SUBMENU_NLSUBSCRIBER"), "subscribers");
		AZMailerAdminInterfaceHelper::addButtonsToToolBar(array(
			array("core.create", "subscriber.apply", 'apply', 'JTOOLBAR_APPLY', false), /*save&stay*/
			array("core.create", "subscriber.save", 'save', 'JTOOLBAR_SAVE', false), /*save&close*/
			//array("core.create", "subscriber.duplicate", 'save-copy', 'JTOOLBAR_SAVE_AS_COPY', false), /*save_as_copy*/
			array("core.manage", "subscriber.display", 'cancel', 'JTOOLBAR_CANCEL', false), /*cancel*/
		));
		$JI->set("hidemainmenu", 1);//blocks main-menu
	}

	/**
	 * Alias for save
	 */
	public function apply() {
		$this->save(true);
	}

	/**
	 * @param bool $isApply
	 */
	public function save($isApply = false) {
		/** @var \AZMailer\AZMailerCore $AZMAILER */
		global $AZMAILER;
		\JSession::checkToken() or jexit('Invalid Token');
		/** @var AZMailerModelSubscriber $model */
		$model = $this->getModel();
		/** @var $JI \JInput */
		$JI = \JFactory::getApplication()->input;
		$model->saveSpecificItem($JI->getArray($_POST));
		if (!$isApply) {
			$AZMAILER->getController()->setRedirect(JRoute::_('index.php?option=' . $AZMAILER->getOption("com_name") . '&task=subscriber.display', false));
		} else {
			$newid = $model->getState($model->getName() . '.id');
			$AZMAILER->getController()->setRedirect(JRoute::_('index.php?option=' . $AZMAILER->getOption("com_name") . '&task=subscriber.edit&cid=' . $newid, false));
		}
	}

	/**
	 * Delete
	 */
	public function delete() {
		global $AZMAILER;
		/** @var AZMailerModelQueuemanager $model */
		$model = $this->getModel();
		/** @var $JI \JInput */
		$JI = \JFactory::getApplication()->input;
		$cid = $JI->get("cid", false, "array");
		$model->removeSpecificItems($cid);
		$AZMAILER->getController()->setRedirect(JRoute::_('index.php?option=' . $AZMAILER->getOption("com_name") . '&task=subscriber.display', false));
	}

	/**
	 * Import from file
	 */
	public function import() {
		/** @var $JI \JInput */
		$JI = \JFactory::getApplication()->input;
		$this->state = $this->get('State');
		parent::display("import");
		//
		AZMailerAdminInterfaceHelper::setHeaderTitle(\JText::_("COM_AZMAILER_SUBMENU_NLSUBSCRIBER"), "subscribers");
		AZMailerAdminInterfaceHelper::addButtonsToToolBar(array(
			array("core.create", "subscriber.importSubscribers", 'import', 'COM_AZMAILER_TOOLBARBUTTON_IMPORT', false), /*save&close*/
			array("core.manage", "subscriber.display", 'cancel', 'JTOOLBAR_CANCEL', false), /*cancel*/
		));
		$JI->set("hidemainmenu", 1);//blocks main-menu
	}


}
