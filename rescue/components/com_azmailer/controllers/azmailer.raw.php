<?php
/**
 * @package    AZMailer
 * @author     Adam Jakab {@link http://www.alfazeta.com}
 * @author     Created on 09-Feb-2013
 * @license    GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.controller');
use AZMailer\Helpers\AZMailerNewsletterHelper;
use AZMailer\Helpers\AZMailerLocationHelper;
use AZMailer\Helpers\AZMailerSubscriberHelper;
use AZMailer\Helpers\AZMailerQueuemanagerHelper;
use AZMailer\Entities\AZMailerSubscriber;
use AZMailer\Entities\AZMailerQueueItem;


/**
 * AZMailer Controller - RAW REQUESTS
 */
class AZMailerControllerAZMailer extends \JControllerLegacy {
	/**
	 * @param array $config
	 */
	function __construct($config = array()) {
		parent::__construct($config);
		ob_clean();
		\JFactory::getDocument()->setMimeEncoding('application/json');
	}

	/**
	 * @param bool  $cachable
	 * @param array $urlparams
	 * @return JControllerLegacy|void
	 */
	public function display($cachable = false, $urlparams = array()) {
		global $AZMAILER;
		\JFactory::getDocument()->setMimeEncoding('text/plain');
		$answer = array();
		$answer[] = "The task you have requested does not exist!";
		$answer[] = "Task: " . $AZMAILER->getOption("ctrl.task");
		echo implode("\n", $answer);
	}

	public function getSelectOptionsCountries() {
		$answer = new \stdClass();
		$answer->result = AZMailerLocationHelper::getSelectOptions_Countries(JText::_("SELECT_COUNTRY"));//JText::_("SELECT_COUNTRY")
		$answer->errors = array();
		echo json_encode($answer);
	}

	public function getSelectOptionsRegions() {
		$answer = new \stdClass();
		$JI = \JFactory::getApplication()->input;
		$country_id = $JI->getInt("country_id", 0);
		$answer->result = AZMailerLocationHelper::getSelectOptions_Regions(JText::_("SELECT_REGION"), $country_id);
		$answer->errors = array();
		echo json_encode($answer);
	}

	public function getSelectOptionsProvinces() {
		$answer = new \stdClass();
		$JI = \JFactory::getApplication()->input;
		$region_id = $JI->getInt("region_id", 0);
		$answer->result = AZMailerLocationHelper::getSelectOptions_Provinces(JText::_("SELECT_PROVINCE"), $region_id);
		$answer->errors = array();
		echo json_encode($answer);
	}

	public function registerNewsletterSubscriber() {
		$answer = new \stdClass();
		$answer->errors = array();
		$JI = \JFactory::getApplication()->input;
		$subscriber = array();

		//FIRSTNAME
		$subscriber["nls_firstname"] = $JI->getString("nls_firstname", "");
		if (empty($subscriber["nls_firstname"])) {
			array_push($answer->errors, array("field"=>"nls_firstname", "message"=>JText::_("COM_AZMAILER_SUBSCR_ERR_NAME")));
		}
		//LASTNAME
		$subscriber["nls_lastname"] = $JI->getString("nls_lastname", "");
		if (empty($subscriber["nls_lastname"])) {
			array_push($answer->errors, array("field"=>"nls_lastname", "message"=>JText::_("COM_AZMAILER_SUBSCR_ERR_LASTNAME")));
		}
		//EMAIL
		$subscriber["nls_email"] = $JI->getString("nls_email", "");
		if (!AZMailerSubscriberHelper::checkIfEmailSyntaxIsValid($subscriber["nls_email"])) {
			array_push($answer->errors, array("field"=>"nls_email", "message"=>JText::_("COM_AZMAILER_SUBSCR_ERR_MAIL")));
		}
		if (!AZMailerSubscriberHelper::checkIfNLSMailIsAvailable($subscriber["nls_email"])) {
			array_push($answer->errors, array("field"=>"nls_email", "message"=>JText::_("COM_AZMAILER_SUBSCR_ERR_MAIL_REGISTERED")));
		}

		//LOCATION - todo: default config is missing
		$subscriber["nls_country_id"] = $JI->getInt("nls_country_id", null);
		if($subscriber["nls_country_id"]!==null && $subscriber["nls_country_id"]==0) {
			array_push($answer->errors, array("field"=>"nls_country_id", "message"=>JText::_("COM_AZMAILER_SUBSCR_ERR_COUNTRY_NOT_SELECTED")));
		}
		$subscriber["nls_region_id"] = $JI->getInt("nls_region_id", null);
		if($subscriber["nls_region_id"]!==null && $subscriber["nls_region_id"]==0) {
			array_push($answer->errors, array("field"=>"nls_region_id", "message"=>JText::_("COM_AZMAILER_SUBSCR_ERR_REGION_NOT_SELECTED")));
		}
		$subscriber["nls_province_id"] = $JI->getInt("nls_province_id", null);
		if($subscriber["nls_province_id"]!==null && $subscriber["nls_province_id"]==0) {
			array_push($answer->errors, array("field"=>"nls_province_id", "message"=>JText::_("COM_AZMAILER_SUBSCR_ERR_PROVINCE_NOT_SELECTED")));
		}

		//CATEGORIES - entity sets default values automatically if non supplied

		//PRIVACY
		$subscriber["nls_privacy"] = $JI->getInt("nls_privacy", null);
		if($subscriber["nls_privacy"]!==null && $subscriber["nls_privacy"]!=1) {
			array_push($answer->errors, array("field"=>"nls_privacy", "message"=>JText::_("COM_AZMAILER_SUBSCR_ERR_PRIVACY_NOT_ACCEPTED")));
		}

		//REGISTER
		if (!count($answer->errors)) {
			$NLS = new AZMailerSubscriber($subscriber);
			if ($NLS->setup(true)) {
				$NLS->sync();
				$answer->result = JText::_("COM_AZMAILER_SUBSCR_REGISTERED_OK");
			} else {
				array_push($answer->errors, array("field"=>"generic", "message"=>JText::_("COM_AZMAILER_SUBSCR_NOT_SETUP")));
			}
		}
		echo json_encode($answer);
	}

	/**
	 * Called from newsletter as img src - registers MQI as read
	 */
	public function domqirc() {
		$JI = \JFactory::getApplication()->input;
		$CTRL = $JI->getString("ctrl", "");
		if (!empty($CTRL)) {
			$CA = explode(":", $CTRL);
			if (is_array($CA) && count($CA)==2) {
				$mqiid = base64_decode(urldecode($CA[0]));//this is the MQI id
				$MQI = AZMailerQueuemanagerHelper::getMQIById($mqiid);
				if (!is_null($MQI)) {
					$MQI = new AZMailerQueueItem($MQI);
					$CTRLSTRSHA1MD5 = sha1(md5(strtolower($MQI->get("mq_to").'#'.$MQI->get("mq_date").'#'.$MQI->get("mq_type").'#'.$MQI->get("mq_typeid"))));
					if ($CA[1] == $CTRLSTRSHA1MD5) {
						$MQI->markMailQueueItemRead();
					}
				}
			}
		}
		//OUTPUT - newsletter is expecting an image
		\JFactory::getDocument()->setMimeEncoding('image/gif');
		$im = imagecreate (1,1);
		imagegif($im);
		imagedestroy($im);
	}

	/**
	 * TODO: all links created to attachments, removeMe, domirc should have parameters encrypted with subscribers' key
	 * this means that all subscribers should have a key and it should be updated monthly (if user does not have mail in queue + 1week)
	 * we need a helper for this
	 */
	public function getAttachment() {
		/** @var AZMailer\AZMailerCore */
		global $AZMAILER;
		$JI = \JFactory::getApplication()->input;
		$CTRL = $JI->getString("ctrl", "");
		$filename = $JI->getString("file", "");
		$nlid = $JI->getInt("nlid", 0);
		$serveFile = false;
		if(!empty($CTRL) && !empty($filename) && !empty($nlid)) {
			$filename = base64_decode(urldecode($filename));
			$email = null;
			$controlString = null;
			if(preg_match('/:/', $CTRL)) {
				$CA = explode(":", $CTRL);
				if(isset($CA[0])&&isset($CA[1])) {
					$email = base64_decode(urldecode($CA[0]));
					$controlString = $CA[1];
				}
			} else {
				$email = base64_decode(urldecode($CTRL));
				$controlString = "Undefined";
			}

			if(!empty($email)&&!empty($controlString)) {
				//get NEWSLETTER data
				$NL = AZMailerNewsletterHelper::getNewsletter($nlid);
				if (!is_null($NL)) {
					$ATTACHMENTS = json_decode(base64_decode($NL->nl_attachments));
					if (is_object($ATTACHMENTS)&&isset($ATTACHMENTS->attachments)&&is_array($ATTACHMENTS->attachments)&&count($ATTACHMENTS->attachments)) {
						//ok we have attachments
						//we need to check if user trying to access file is a registered subscriber
						$NLS = AZMailerSubscriberHelper::getNewsletterSubscriberByMail($email);
						if (!is_null($NLS)) {
							$NLS = new AZMailerSubscriber($NLS);
							//todo: make checks to see if user is a selected reciever for newsletter that has this attachment
							if(true) {
								$serveFile = true;
							}
						} else {
							//only email supplied - could be an admin user
							if(AZMailerSubscriberHelper::checkIfMailisAdminUser($email)) {
								$serveFile = true;
							}
						}
					}
				}
			}
		}

		$fileFullPath = false;
		$fileType = false;
		if($serveFile && isset($ATTACHMENTS)) {
			if (is_object($ATTACHMENTS)&&isset($ATTACHMENTS->attachments)&&is_array($ATTACHMENTS->attachments)&&count($ATTACHMENTS->attachments)) {
				foreach($ATTACHMENTS->attachments as &$attachment) {
					if($attachment->filename == $filename) {
						$attachmentFolder = $AZMAILER->getOption("newsletter_attachment_base");
						$fileFullPath = JPATH_ROOT.DS.$attachmentFolder.DS.$attachment->filename;
						$fileType = $attachment->type;
						$fileName = $attachment->name;
						break;
					}
				}
			}
		}

		if($serveFile && $fileFullPath && $fileType) {
			//\JFactory::getDocument()->setMimeEncoding('text/plain');
			//echo("serving file: " . $fileFullPath);
			$mimesOpenInBrowser = array(
				"image/jpeg", "application/pdf"
			);
			header('Content-Type: ' . $fileType);
			if (!in_array($fileType, $mimesOpenInBrowser)) {
				header("Content-disposition: attachment;filename=" . $filename);
			}
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: private');
			header('Content-Length: ' . filesize($fileFullPath));
			ob_clean();
			ob_end_flush();
			//todo: could this cause problems(out of memory) on big files?
			readfile($fileFullPath);
		} else {
			\JFactory::getDocument()->setMimeEncoding('text/plain');
			echo("File not found.");
		}
	}

}
