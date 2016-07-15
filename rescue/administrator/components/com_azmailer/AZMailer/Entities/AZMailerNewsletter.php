<?php
namespace AZMailer\Entities;

use AZMailer\Helpers\AZMailerDateHelper;
use AZMailer\Helpers\AZMailerSubscriberHelper;
use AZMailer\Helpers\AZMailerTemplateHelper;

/**
 * @package    AZMailer
 * @subpackage Entities
 * @author     Adam Jakab {@link http://www.alfazeta.com}
 * @author     Created on 09-Feb-2013
 * @license    GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');

/**
 * Class AZMailerNewsletter
 * @package AZMailer\Entities
 */
class AZMailerNewsletter extends AZMailerEntity {
	//private $locationData;
	/**
	 * @param null $entityData
	 * @param null $entityOptions
	 */
	function __construct($entityData = null, $entityOptions = null) {
		parent::__construct($entityData, $entityOptions);
		$this->checkSetupDefaultValues();
	}

	private function checkSetupDefaultValues() {
		global $AZMAILER;
		if ($this->get("id") == 0) {
			$defTplId = AZMailerTemplateHelper::getTemplateIdByCode("default");
			$defTpl = AZMailerTemplateHelper::getTemplateById($defTplId);
			//
			$this->set("nl_create_date", AZMailerDateHelper::now());
			$this->set("nl_send_date", 0);
			$this->set("nl_title", $defTpl->tpl_title);
			$this->set("nl_title_internal", $defTpl->tpl_title);
			$this->set("nl_email_from", $AZMAILER->getOption("mail_default_from"));
			$this->set("nl_email_from_name", $AZMAILER->getOption("mail_default_from_name"));
			$this->set("nl_template_id", $defTpl->id);
			$this->set("nl_template_substitutions", base64_encode(json_encode(new \stdClass())));
			$this->set("nl_sendto_selections", base64_encode(json_encode(new \stdClass())));
			$this->set("nl_sendto_additional", base64_encode(json_encode(new \stdClass())));
			$this->set("nl_attachments", base64_encode(json_encode(new \stdClass())));
		}
	}

	/**
	 * @param string $mail
	 * @param string $type
	 * @param int  $priority
	 * @param string $firstname
	 * @param string $lastname
	 * @return \stdClass
	 */
	public function sendToSingleContact($mail = null, $type = null, $priority = 0, $firstname = null, $lastname = null) {
		$answer = new \stdClass();
		$answer->result = "";
		$answer->errors = array();

		/** @var \stdClass $MO A generic mail Object (used to set up queue item entity) */
		$MO = new \stdClass();
		//$MO->newsletterid = $this->get("id");
		$MO->mq_type = ($type ? $type : "newsletter");//could be "newsletter(test)"
		$MO->mq_typeid = $this->get("id");//get it from NL
		$MO->mq_priority = $priority;//0===send it right away
		$MO->mq_from = $this->get("nl_email_from");//get it from NL
		$MO->mq_from_name = $this->get("nl_email_from_name");//get it from NL
		$MO->mq_to = $mail;
		$MO->mq_subject = $this->get("nl_title");//get it from NL
		//TODO: must decide when to create+save html body in database and when not to do it - here it should be option driven
		//$MO->mq_body = "";//set to empty - it will be created on the fly when sending
		//$MO->mq_body_txt = "";//set to empty - it will be created on the fly when sending

		//SUBST DATA - JSON string
		$MQSUBST = array();
		$MQSUBST["EMAIL"] = $mail;

		//if neither firstname nor lastname supplied let's try to get it from subscriber
		if (!($firstname && $lastname) && ($NLS = AZMailerSubscriberHelper::getNewsletterSubscriberByMail($mail))) {
			$firstname = $NLS->nls_firstname;
			$lastname = $NLS->nls_lastname;
		}
		$MQSUBST["FIRSTNAME"] = ($firstname ? $firstname : '');
		$MQSUBST["LASTNAME"] = ($lastname ? $lastname : '');
		//let's make a FULLNAME one as well
		$MQSUBST["FULLNAME"] = $MQSUBST["FIRSTNAME"] . (!empty($MQSUBST["LASTNAME"]) ? " " . $MQSUBST["LASTNAME"] : "");

		$MO->mq_substitutions = json_encode($MQSUBST);


		//ATTACHMENTS - comma separated values !!! $AZMAILER->getOption("newsletter_attachment_base");
		$MO->mq_attachments = $this->get("nl_attachments");


		//trigger AZMailer System plugin to queue mail
		if (IS_J3) {
			$dispatcher = \JEventDispatcher::getInstance();//J!3
		} else {
			$dispatcher = \JDispatcher::getInstance();//J!25
		}
		$plgResp = $dispatcher->trigger("AZMSYSPLG_queueMail", array($MO));

		//
		if (isset($plgResp[0]) && $plgResp[0] === true) {
			$answer->result = \JText::sprintf('COM_AZMAILER_NEWSLETTER_MSG_TEST_SENT', $mail);//SENT
		} else {
			$answer->result = \JText::sprintf('COM_AZMAILER_NEWSLETTER_MSG_TEST_SENT_DEFERRED', $mail, json_encode($plgResp));//error - this can be misleading - should use $plgResp only
		}
		return ($answer);
	}

	//------------------------------------------------PRIVATE

	/**
	 * @return bool
	 */
	public function save() {
		return ($this->_save());
	}

	/**
	 * @return bool
	 */
	private function _save() {
		$answer = false;
		/** @var \JTable $table */
		$table = \JTable::getInstance('azmailer_newsletter', 'Table');
		if ($table->save($this->data)) {
			if (!$this->get("id")) {
				$db = $table->getDbo();
				$this->set("id", $db->insertid());
			}
			$answer = true;
		}
		return ($answer);
	}
}