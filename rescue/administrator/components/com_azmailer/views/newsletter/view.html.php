<?php
/**
 * @package    AZMailer
 * @author     Adam Jakab {@link http://www.alfazeta.com}
 * @author     Created on 09-Feb-2013
 * @license    GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
use AZMailer\Core\AZMailerView;
use AZMailer\Entities\AZMailerNewsletter;
use AZMailer\Helpers\AZMailerAdminInterfaceHelper;
use AZMailer\Helpers\AZMailerDateHelper;
use AZMailer\Helpers\AZMailerNewsletterHelper;

/**
 * Class AZMailerViewNewsletter
 */
class AZMailerViewNewsletter extends AZMailerView {
	/**
	 * @param null $tpl
	 * @return mixed|void
	 * @throws Exception
	 */
	public function display($tpl = null) {
		$this->items = $this->get('Items');
		foreach ($this->items as &$item) {
			$item = new AZMailerNewsletter($item);
		}

		$this->filters = $this->get('Filters');
		$this->pagination = $this->get('Pagination');
		$this->state = $this->get('State');
		//
		if (count($errors = $this->get('Errors'))) {
			throw new \Exception(implode('<br />', $errors), 500);
		}

		parent::display();
		//
		AZMailerAdminInterfaceHelper::setHeaderTitle(\JText::_("COM_AZMAILER_SUBMENU_NEWSLETTER"), "newsletter");
		AZMailerAdminInterfaceHelper::addButtonsToToolBar(array(
			array("core.create", "newsletter.edit", 'new', 'JTOOLBAR_NEW', false),
			array("core.create", "newsletter.duplicate", 'copy', 'COM_AZMAILER_TOOLBARBUTTON_DUPLICATE', false), /*save_as_copy*/
			array("core.delete", "newsletter.delete", 'delete', 'JTOOLBAR_DELETE', true)
		));
	}

	public function edit() {
		/** @var AZMailerModelNewsletter $model */
		$model = $this->getModel();
		/** @var $JI \JInput */
		$JI = \JFactory::getApplication()->input;
		$id = $JI->getInt("cid", 0);
		$this->item = new AZMailerNewsletter($model->getSpecificItem($id));
		$this->state = $this->get('State');
		parent::display("edit");
		//
		AZMailerAdminInterfaceHelper::setHeaderTitle(\JText::_("COM_AZMAILER_SUBMENU_NEWSLETTER"), "newsletter");
		AZMailerAdminInterfaceHelper::addButtonsToToolBar(array(
			array("core.create", "newsletter.apply", 'apply', 'JTOOLBAR_APPLY', false), /*save&stay*/
			array("core.create", "newsletter.save", 'save', 'JTOOLBAR_SAVE', false), /*save&close*/
			array("core.manage", "newsletter.display", 'cancel', 'JTOOLBAR_CANCEL', false), /*cancel*/
		));
		$JI->set("hidemainmenu", 1);//blocks main-menu
	}

	public function apply() {
		$this->save(true);
	}

	/**
	 * @param bool $isApply
	 * @throws Exception
	 */
	public function save($isApply = false) {
		global $AZMAILER;
		\JSession::checkToken() or jexit('Invalid Token');
		/** @var AZMailerModelNewsletter $model */
		$model = $this->getModel();
		/** @var $JI \JInput */
		$JI = \JFactory::getApplication()->input;
		$model->saveSpecificItem($JI->getArray($_POST));
		if (!$isApply) {
			$AZMAILER->getController()->setRedirect(JRoute::_('index.php?option=' . $AZMAILER->getOption("com_name") . '&task=newsletter.display', false));
		} else {
			$newid = $model->getState($model->getName() . '.id');
			$AZMAILER->getController()->setRedirect(JRoute::_('index.php?option=' . $AZMAILER->getOption("com_name") . '&task=newsletter.edit&cid=' . $newid, false));
		}
	}

	public function send() {
		/** @var AZMailer\AZMailerCore */
		global $AZMAILER;
		$JI = \JFactory::getApplication()->input;
		$nlid = $JI->getInt("newsletter_id", 0);
		$newsletter = AZMailerNewsletterHelper::getNewsletter($nlid);
		if ($newsletter) {
			$newsletter = new AZMailerNewsletter($newsletter);
			//
			$NL_CATSEL = json_decode(base64_decode($newsletter->get("nl_sendto_selections")));
			if (!is_object($NL_CATSEL)) {
				$NL_CATSEL = new stdClass();
			}
			$NL_ADDITIONAL = json_decode(base64_decode($newsletter->get("nl_sendto_additional")));
			if (!is_object($NL_ADDITIONAL)) {
				$NL_ADDITIONAL = new stdClass();
				$NL_ADDITIONAL->subscribers = array();
			}
			//
			$NL_CONTACTS = array();
			//LET'S ADD ADDITIONAL CONTACTS RIGHT AWAY
			if (isset($NL_ADDITIONAL->subscribers) && count($NL_ADDITIONAL->subscribers)) {
				$NL_CONTACTS = array_merge($NL_CONTACTS, $NL_ADDITIONAL->subscribers);
			}
			//LET'S ADD CATEGORY SELECTED CONTACTS
			$NLS_SELECTION_OBJECT = AZMailerNewsletterHelper::getNLSubscribersForCategorySelections($NL_CATSEL);//returns ->list and ->sql
			$CATSELCONTACTS = $NLS_SELECTION_OBJECT->list;
			if (count($CATSELCONTACTS)) {
				$NL_CONTACTS = array_merge($NL_CONTACTS, $CATSELCONTACTS);
			}
			//LET'S CLEAN UP $NL_CONTACTS FROM DUPLICATES
			$NL_CONTACTS = AZMailerNewsletterHelper::cleanupArrayOfObjectsFromDuplicates($NL_CONTACTS, "nls_email");
			//
			if (count($NL_CONTACTS)) {
				foreach ($NL_CONTACTS as $NLC) {
					$newsletter->sendToSingleContact($NLC->nls_email, "newsletter", 1, $NLC->nls_firstname, $NLC->nls_lastname);
				}
				//we are finished so we register newsletter as sent and we can go home
				//UPDATE NEWSLETTER
				$newsletter->set("nl_send_date", AZMailerDateHelper::now());
				$newsletter->set("nl_sendcount", count($NL_CONTACTS));
				$newsletter->save();
				$msg = "Newsletter has been inserted into the queue for " . count($NL_CONTACTS) . " subscribers.";
			} else {
				$msg = "No contacts found!";
			}
		} else {
			$msg = "Newsletter not found!";
		}
		$AZMAILER->getController()->setRedirect('index.php?option=' . $AZMAILER->getOption("com_name") . '&task=newsletter.display', $msg);
	}


	public function delete() {
		global $AZMAILER;
		/** @var AZMailerModelNewsletter $model */
		$model = $this->getModel();
		/** @var $JI \JInput */
		$JI = \JFactory::getApplication()->input;
		$cid = $JI->get("cid", false, "array");
		$model->removeSpecificItems($cid);
		$AZMAILER->getController()->setRedirect(JRoute::_('index.php?option=' . $AZMAILER->getOption("com_name") . '&task=newsletter.display', false));
	}

	public function duplicate() {
		global $AZMAILER;
		/** @var AZMailerModelNewsletter $model */
		$model = $this->getModel();
		/** @var $JI \JInput */
		$JI = \JFactory::getApplication()->input;
		$cid = $JI->get("cid", false, "array");
		$model->duplicateItem($cid);
		$AZMAILER->getController()->setRedirect(JRoute::_('index.php?option=' . $AZMAILER->getOption("com_name") . '&task=newsletter.display', false));
	}


}
