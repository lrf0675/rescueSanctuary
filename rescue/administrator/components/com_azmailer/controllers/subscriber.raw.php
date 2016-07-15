<?php
/**
 * @package    AZMailer
 * @author     Adam Jakab {@link http://www.alfazeta.com}
 * @author     Created on 09-Feb-2013
 * @license    GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
use AZMailer\Core\AZMailerController;
use AZMailer\Helpers\AZMailerLocationHelper;
use AZMailer\Helpers\AZMailerSubscriberHelper;

/**
 * Controller for Subscribers - RAW REQUESTS
 * contoller is called with "format=raw"
 * No View is involved - works with model and outputs clean data
 */
class AZMailerControllerSubscriber extends AZMailerController {
	/** @var $model \AZMailerModelSubscriber */
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


	public function showFailedCheckLog() {
		\JFactory::getDocument()->setMimeEncoding('text/plain');
		$answer = array();
		$JI = \JFactory::getApplication()->input;
		$nlsid = $JI->getInt("nlsid", null);
		$answer[] = $this->model->getCheckLogs($nlsid);
		echo implode("\n", $answer);
	}

	public function getSelectOptionsCountries() {
		$answer = new \stdClass();
		$answer->result = AZMailerLocationHelper::getSelectOptions_Countries();
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

	public function check_nls_data() {
		$answer = new \stdClass();
		$answer->errors = array();
		$JI = \JFactory::getApplication()->input;
		$nlsid = $JI->getInt("id", 0);
		//FIRSTNAME
		$firstname = $JI->getString("nls_firstname", "");
		if (empty($firstname)) {
			array_push($answer->errors, array("field" => "nls_firstname", "message" => JText::_("COM_AZMAILER_SUBSCR_ERR_NAME")));
		}
		//EMAIL
		$email = $JI->getString("nls_email", "");
		if (!AZMailerSubscriberHelper::checkIfEmailSyntaxIsValid($email)) {
			array_push($answer->errors, array("field" => "nls_email", "message" => JText::_("COM_AZMAILER_SUBSCR_ERR_MAIL")));
		}
		if (!AZMailerSubscriberHelper::checkIfNLSMailIsAvailable($email, $nlsid)) {
			array_push($answer->errors, array("field" => "nls_email", "message" => JText::_("COM_AZMAILER_SUBSCR_ERR_MAIL_REGISTERED")));
		}
		echo json_encode($answer);
	}

	public function importSubscribers() {
		$answer = new \stdClass();
		$answer->errors = array();
		$JI = \JFactory::getApplication()->input;
		$answer->post = array();
		$answer->post["uploadedfile"] = $JI->files->get('nls_contacts_file', null);
		$answer->post["defaults"] = array();
		$answer->post["defaults"]["nls_overwrite_existing"] = $JI->getInt('nls_overwrite_existing', 3);//merge by default
		$answer->post["defaults"]["nls_blacklisted"] = $JI->getString('nls_blacklisted', "N");
		$answer->post["defaults"]["nls_country_id"] = $JI->getInt('nls_country_id', 0);
		$answer->post["defaults"]["nls_region_id"] = $JI->getInt('nls_region_id', 0);
		$answer->post["defaults"]["nls_province_id"] = $JI->getInt('nls_province_id', 0);
		//nls_cat_1..5[] - passed as array of numbers
		$rawPostData = $JI->getArray($_POST);
		$answer->post["defaults"]["nls_cat_1"] = (isset($rawPostData["nls_cat_1"]) ? $rawPostData["nls_cat_1"] : array());
		$answer->post["defaults"]["nls_cat_2"] = (isset($rawPostData["nls_cat_2"]) ? $rawPostData["nls_cat_2"] : array());
		$answer->post["defaults"]["nls_cat_3"] = (isset($rawPostData["nls_cat_3"]) ? $rawPostData["nls_cat_3"] : array());
		$answer->post["defaults"]["nls_cat_4"] = (isset($rawPostData["nls_cat_4"]) ? $rawPostData["nls_cat_4"] : array());
		$answer->post["defaults"]["nls_cat_5"] = (isset($rawPostData["nls_cat_5"]) ? $rawPostData["nls_cat_5"] : array());
		//
		$answer = $this->model->importSubscribersFromUploadedFile($answer);
		echo json_encode($answer);
		//print_r($answer);
	}


}
