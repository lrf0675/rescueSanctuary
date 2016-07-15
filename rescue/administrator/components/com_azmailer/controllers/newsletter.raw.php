<?php
/**
 * @package    AZMailer
 * @author     Adam Jakab {@link http://www.alfazeta.com}
 * @author     Created on 09-Feb-2013
 * @license    GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
use AZMailer\Core\AZMailerController;
use AZMailer\Entities\AZMailerNewsletter;
use AZMailer\Helpers\AZMailerLocationHelper;
use AZMailer\Helpers\AZMailerNewsletterHelper;
use AZMailer\Helpers\AZMailerTemplateHelper;

/**
 * Controller for Newsletter - RAW REQUESTS
 * contoller is called with "format=raw"
 * No View is involved - works with model and outputs clean data
 */
class AZMailerControllerNewsletter extends AZMailerController {
	/** @var $model \AZMailerModelNewsletter */
	private $model;

	/**
	 * @param array $config
	 */
	function __construct($config = array()) {
		global $AZMAILER;
		parent::__construct($config);
		\JFactory::getDocument()->setMimeEncoding('application/json');
		$this->model = $this->getModel($AZMAILER->getOption("controller"));
	}

	/**
	 * @param bool $cachable
	 * @param bool $urlparams
	 * @return JController|void
	 */
	public function display($cachable = false, $urlparams = false) {
		global $AZMAILER;
		\JFactory::getDocument()->setMimeEncoding('text/plain');
		$answer = array();
		$answer[] = "The task you have requested does not exist!";
		$answer[] = "Task: " . $AZMAILER->getOption("ctrl.task");
		echo implode("\n", $answer);
	}

	public function previewNewsletter() {
		\JFactory::getDocument()->setMimeEncoding('text/html');
		$JI = \JFactory::getApplication()->input;
		$nlid = $JI->getInt("nlid", null);
		$newsletter = AZMailerNewsletterHelper::getNewsletter($nlid);
		$newsletter = new AZMailerNewsletter($newsletter);
		$answer = AZMailerNewsletterHelper::getNewsletterSubstitutedContentFromTemplate($newsletter->get("nl_template_id"), $newsletter->get("nl_template_substitutions"));
		echo $answer;
	}

	//----------------------------------------------------

	public function getNewsletterSubstitutedContent() {
		$JI = \JFactory::getApplication()->input;
		$tpl_id = $JI->getInt("tpl_id", 0);
		$tpl_subst = $JI->getString("tpl_subst", base64_encode('{}'));
		$answer = new \stdClass();
		$answer->result = AZMailerNewsletterHelper::getNewsletterSubstitutedContentFromTemplate($tpl_id, $tpl_subst);
		$answer->errors = array();
		echo json_encode($answer);
	}

	public function getNewsletterSimpleTextVersion() {
		$JI = \JFactory::getApplication()->input;
		$tpl_id = $JI->getInt("tpl_id", 0);
		$tpl_subst = $JI->getString("tpl_subst", base64_encode('{}'));
		$answer = new \stdClass();
		$answer->result = AZMailerNewsletterHelper::getNewsletterSimpleTextVersion($tpl_id, $tpl_subst);
		$answer->errors = array();
		echo json_encode($answer);
	}

	public function getNewsletterSendtoData() {
		$JI = \JFactory::getApplication()->input;
		$nl_sendto_selections = $JI->getString("nl_sendto_selections", base64_encode('{}'));
		$nl_sendto_additional = $JI->getString("nl_sendto_additional", base64_encode('{}'));
		$answer = new \stdClass();
		$answer->result = AZMailerNewsletterHelper::getNewsletterSendtoData($nl_sendto_selections, $nl_sendto_additional);
		$answer->errors = array();
		echo json_encode($answer);
	}


	public function uploadNewsletterAttachment() {
		$answer = new \stdClass();
		$answer->errors = array();
		$JI = \JFactory::getApplication()->input;
		$answer->uploadedfile = $JI->files->get('fileToUpload', null);
		$answer->nlid = $JI->getInt('nlid', 0);
		$answer = AZMailerNewsletterHelper::uploadNewsletterAttachment($answer);
		echo json_encode($answer);
	}


	public function removeNewsletterAttachment() {
		$JI = \JFactory::getApplication()->input;
		$nlid = $JI->getInt("nlid", 0);
		$filename = $JI->getString("filename", 0);
		$answer = AZMailerNewsletterHelper::removeNewsletterAttachment($nlid, $filename);
		echo json_encode($answer);
	}

	public function getNewsletterAttachments() {
		$JI = \JFactory::getApplication()->input;
		$nlid = $JI->getInt("nlid", 0);
		$answer = AZMailerNewsletterHelper::getNewsletterAttachments($nlid, \JFactory::getUser()->get("email"));
		echo json_encode($answer);
	}


	public function getSelectOptionsCountries() {
		$answer = new \stdClass();
		$answer->result = AZMailerLocationHelper::getSelectOptions_Countries("COM_AZMAILER_SELECT_ONE");
		$answer->errors = array();
		echo json_encode($answer);
	}

	public function getSelectOptionsRegions() {
		$answer = new \stdClass();
		$JI = \JFactory::getApplication()->input;
		$country_id = $JI->getInt("country_id", 0);
		$answer->result = AZMailerLocationHelper::getSelectOptions_Regions("COM_AZMAILER_SELECT_ONE", $country_id);
		$answer->errors = array();
		echo json_encode($answer);
	}

	public function getSelectOptionsProvinces() {
		$answer = new \stdClass();
		$JI = \JFactory::getApplication()->input;
		$region_id = $JI->getInt("region_id", 0);
		$answer->result = AZMailerLocationHelper::getSelectOptions_Provinces("COM_AZMAILER_SELECT_ONE", $region_id);
		$answer->errors = array();
		echo json_encode($answer);
	}

	public function getSelectOptionsTemplates() {
		$answer = new \stdClass();
		$answer->result = AZMailerTemplateHelper::getSelectOptions_TemplatesForType("newsletter", false);
		$answer->errors = array();
		echo json_encode($answer);
	}

	public function changeNewsletterEditableImage() {
		$answer = new \stdClass();
		$answer->errors = array();
		$JI = \JFactory::getApplication()->input;
		$answer->uploadedfile = $JI->files->get('fileToUpload');
		$answer->elementid = $JI->getInt("elementid", 'unknown');
		$answer->elementattribs = $JI->getString("elementattribs", base64_encode('{}'));
		$answer->elcurrsrc = $JI->getString("elcurrsrc", null);
		$answer = AZMailerNewsletterHelper::changeNewsletterEditableImage($answer);
		echo json_encode($answer);
	}

	public function sendTestNewsletter() {
		$JI = \JFactory::getApplication()->input;
		$nlid = $JI->getInt("newsletter_id", 0);
		$sendmailto = $JI->getString("sendmailto", "");
		$newsletter = AZMailerNewsletterHelper::getNewsletter($nlid);
		if ($newsletter) {
			$newsletter = new AZMailerNewsletter($newsletter);
			$answer = $newsletter->sendToSingleContact($sendmailto, "newsletter(test)", 0);
		} else {
			$answer = new \stdClass();
			$answer->result = "";
			$answer->errors = array("Newsletter not found!");
		}
		echo json_encode($answer);
	}


}
