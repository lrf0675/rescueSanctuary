<?php
/**
 * @package    AZMailer
 * @author     Adam Jakab {@link http://www.alfazeta.com}
 * @author     Created on 09-Feb-2013
 * @license    GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
use AZMailer\Core\AZMailerView;
use AZMailer\Entities\AZMailerQueueItem;
use AZMailer\Helpers\AZMailerAdminInterfaceHelper;

/**
 * Class AZMailerViewQueuemanager
 */
class AZMailerViewQueuemanager extends AZMailerView {
	/**
	 * @param null $tpl
	 * @return mixed|void
	 * @throws Exception
	 */
	public function display($tpl = null) {

		$this->items = $this->get('Items');

		foreach ($this->items as &$item) {
			$item = new AZMailerQueueItem($item);
		}

		$this->filters = $this->get('Filters');
		$this->pagination = $this->get('Pagination');
		$this->state = $this->get('State');

		if (count($errors = $this->get('Errors'))) {
			throw new \Exception(implode('<br />', $errors), 500);
		}

		parent::display();

		AZMailerAdminInterfaceHelper::setHeaderTitle(\JText::_("COM_AZMAILER_SUBMENU_MAILQUEUEMANAGER"), "mailqueue");
		AZMailerAdminInterfaceHelper::addButtonsToToolBar(array(
			array("core.delete", "queuemanager.delete", 'delete', 'JTOOLBAR_DELETE', true),
			array("core.create", "queuemanager.display", 'refresh', 'COM_AZMAILER_TOOLBARBUTTON_REFRESH', false)
		));
	}

	public function delete() {
		global $AZMAILER;
		$model = $this->getModel();
		/** @var $JI \JInput */
		$JI = \JFactory::getApplication()->input;
		$cid = $JI->get("cid", false, "array");
		$model->removeSpecificItems($cid);
		$AZMAILER->getController()->setRedirect(JRoute::_('index.php?option=' . $AZMAILER->getOption("com_name") . '&task=queuemanager.display', false));
	}
}

