<?php
namespace AZMailer\Entities;

use AZMailer\Core\AZMailerQueueManager;
use AZMailer\Helpers\AZMailerDateHelper;
use AZMailer\Helpers\AZMailerNewsletterHelper;
use AZMailer\Helpers\AZMailerStatisticsHelper;
use AZMailer\Helpers\AZMailerSubscriberHelper;

/**
 * @package    AZMailer
 * @subpackage Entities
 * @author     Adam Jakab {@link http://www.alfazeta.com}
 * @author     Created on 09-Feb-2013
 * @license    GNU/GPL
 */
defined('_JEXEC') or die();

/**
 * Class AZMailerQueueItem
 * @package AZMailer\Entities
 */
class AZMailerQueueItem extends AZMailerEntity {
	public static $STATE_SENT = 1;
	public static $STATE_FAILED = 2;
	public static $STATE_UNSENT = 3;
	public static $STATE_REQUEUED = 4;

	/**
	 * @param null $entityData
	 * @param null $entityOptions
	 */
	public function __construct($entityData = null, $entityOptions = null) {
		parent::__construct($entityData, $entityOptions);
		$this->checkSetupDefaultValues();
	}

	private function checkSetupDefaultValues() {
		if (!$this->get("mq_date")) $this->set("mq_date", AZMailerDateHelper::now());
		if (!$this->get("mq_type")) $this->set("mq_type", "unknown");
		if (!$this->get("mq_typeid")) $this->set("mq_typeid", 0);
		if (!isset($this->data->mq_priority)) $this->set("mq_priority", 1);
		if (!$this->get("mq_attachments")) $this->set("mq_attachments", "");
		if (!$this->get("mq_substitutions")) $this->set("mq_substitutions", "[]");
	}

	/**
	 * @return bool
	 */
	public function setup() {
		$answer = $this->_setup();
		return ($answer);
	}

	/**
	 * @return bool
	 */
	private function _setup() {
		global $AZMAILER;

		//FROM
		if (!isset($this->data->mq_from) || empty($this->data->mq_from)) {
			$this->data->mq_from = $AZMAILER->getOption("mail_default_from");
			$this->data->mq_from_name = $AZMAILER->getOption("mail_default_from_name");
		}
		if (!AZMailerSubscriberHelper::checkIfEmailSyntaxIsValid($this->data->mq_from)) {
			return (false);
		}

		//TO
		if (!isset($this->data->mq_to) || !AZMailerSubscriberHelper::checkIfEmailSyntaxIsValid($this->data->mq_to)) {
			return (false);
		}

		//SUBJECT
		if (!isset($this->data->mq_subject) || empty($this->data->mq_subject)) {
			return (false);
		}

		return (true);

	}

	/**
	 * Register MQI in database (sets new id on entity data)
	 * @return bool
	 */
	public function enqueue() {
		return ($this->_enqueue());
	}

	//------------------------------------------------GETTERS
	/**
	 * @return bool
	 */
	private function _enqueue() {
		$answer = false;
		/** @var \JTable $table */
		$table = \JTable::getInstance('azmailer_mail_queue_item', 'Table');
		if ($table->save($this->data)) {
			$db = $table->getDbo();
			$this->set("id", $db->insertid());
			$answer = true;
		}
		return ($answer);
	}

	/**
	 * Register MQI as read
	 * @return bool
	 */
	public function markMailQueueItemRead() {
		$answer = false;
		if ($this->get("mq_has_been_read") != 1) {
			/** @var \JTable $table */
			$table = \JTable::getInstance('azmailer_mail_queue_item', 'Table');
			$data = new \stdClass();
			$data->id = $this->get("id");
			$data->mq_has_been_read = 1;
			if ($table->save($data)) {
				AZMailerStatisticsHelper::registerNewsletterStatistics($this);
				$answer = true;
			}
		} else {
			$answer = true;
		}
		return ($answer);
	}

	/**
	 * Send MQI - ONLY IF PRI===0
	 * @return bool
	 */
	public function send() {
		$answer = true;
		if ($this->get("id") && $this->get("mq_priority") == 0) {
			$AZNLMQM = new AZMailerQueueManager();
			$answer = $AZNLMQM->sendZeroPriorityMail($this->get("id"));
		}
		return ($answer);
	}

	//------------------------------------------------PRIVATE

	/**
	 * Creates and returns txt/html body executing substitutions
	 * @param string $type
	 * @return string
	 */
	public function getSubstitutedBody($type = "html") {
		$this->_createMailBody();
		return (($type == "html" ? $this->get("mq_body") : $this->get("mq_body_txt")));
	}

	/**
	 * Create and set html + text body for mail queue item
	 */
	private function _createMailBody() {
		$html = $this->get("mq_body");
		$text = $this->get("mq_body_txt");
		if (empty($html) || empty($text)) {
			if (($NL = AZMailerNewsletterHelper::getNewsletter($this->get("mq_typeid")))) {
				if (empty($html)) {
					$html = AZMailerNewsletterHelper::getNewsletterSubstitutedContentFromTemplate($NL->nl_template_id, $NL->nl_template_substitutions);
				}
				if (empty($text)) {
					$text = $NL->nl_textversion;
					if (empty($text)) {
						$text = strip_tags($html);
						$text = preg_replace('/\{NEWSLETTERREMOVEME\}/', '{NEWSLETTERREMOVEMETXT}', $text);
					}
				}
				$html = $this->doSubstitutionsOnBlob($html, json_decode($this->get("mq_substitutions")));
				$text = $this->doSubstitutionsOnBlob($text, json_decode($this->get("mq_substitutions")));
				$this->set("mq_body", $html);
				$this->set("mq_body_txt", $text);
			}
		}
	}

	/**
	 * @param string $blob
	 * @param array $substitutions
	 * @param string $email
	 * @return mixed
	 */
	private function doSubstitutionsOnBlob($blob, $substitutions = array(), $email = null) {
		global $AZMAILER;
		if (count($substitutions)) {
			foreach ($substitutions as $SK => $SV) {
				$blob = str_replace('{' . $SK . '}', $SV, $blob);
			}
		}
		$email = (!empty($email) ? $email : $this->get("mq_to"));
		if (!empty($email)) {
			//DEFAULT NEWSLETTER REMOVE ME VALUES (TXT/HTML)
			$removeMeUrl = AZMailerNewsletterHelper::getNewsletterRemoveMeUrlForMail($email);
			//HTML VERSION
			$removemehtml = $AZMAILER->getOption("nl_removeme_html");
			$removeme_color = $AZMAILER->getOption("nl_removeme_linkcolor");
			if ($removemehtml) {
				$removemehtml = '<a style="color:' . $removeme_color . '" href="' . $removeMeUrl . '" target="_blank">' . $removemehtml . '</a>';
				$blob = str_replace('{NEWSLETTERREMOVEME}', $removemehtml, $blob);
			}
			//TEXT VERSION
			$removemetxt = $AZMAILER->getOption("nl_removeme_text");
			if ($removemetxt) {
				$removemetxt = $removemetxt . "\n" . $removeMeUrl;
				$blob = str_replace('{NEWSLETTERREMOVEMETXT}', $removemetxt, $blob);
			}
		}
		return ($blob);
	}

	/**
	 * @return string
	 */
	public function getCombinedSender() {
		return ("" .
			($this->get("mq_from_name") ? $this->get("mq_from_name") : "") .
			($this->get("mq_from_name") ? htmlspecialchars(" <") : "") .
			$this->get("mq_from") .
			($this->get("mq_from_name") ? htmlspecialchars(">") : "")
		);
	}

	/**
	 * @return int
	 */
	public function getState() {
		if ($this->get("mq_state") == 1) {//SENT
			return (self::$STATE_SENT);
		} else if ($this->get("mq_state") == 2) {//PERMANENT FAIL
			return (self::$STATE_FAILED);
		} else {
			if ($this->get("mq_send_attempt_count") == 0) {//NOT YET SENT
				return (self::$STATE_UNSENT);
			} else {
				return (self::$STATE_REQUEUED);
			}
		}
	}
}