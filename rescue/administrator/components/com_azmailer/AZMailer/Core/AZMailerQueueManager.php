<?php
namespace AZMailer\Core;

use AZMailer\Entities\AZMailerQueueItem;
use AZMailer\Helpers\AZMailerDateHelper;
use AZMailer\Helpers\AZMailerMimeHelper;
use AZMailer\Helpers\AZMailerNewsletterHelper;
use AZMailer\Helpers\AZMailerQueuemanagerHelper;

//use AZMailer\Helpers\AZMailerLocationHelper;
//use AZMailer\Helpers\AZMailerCategoryHelper;
/**
 * @package    AZMailer
 * @subpackage Core
 * @author     Adam Jakab {@link http://www.alfazeta.com}
 * @author     Created on 09-Feb-2013
 * @license    GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');

/**
 * Class AZMailerQueueManager
 * @package AZMailer\Core
 */
class AZMailerQueueManager {
	private $MQID = 1;//the mail queue ID in db
	private $maxQueueBlockedTime = 300;//if queue has been blocked for longer than this Secs we unblock it - PARAM(mq_max_blocked_time_sec)
	private $priorities = array();

	private $HELO = 'localhost';//used for server greeting - "localhost" will be refused by lots of MS - so this is now PARAM(mail_default_helo)
	//
	private $AZNLMQM_maxRuntime = 30; //this class will be allowed to run for max this number of seconds - PARAM(mq_max_runtime_sec)
	private $AZNLMQM_runtimeMargin = 10;//we will take away this number of seconds from maxRuntime to give us some margin
	private $AZNLMQM_startRuntime = null;
	//
	private $SMTP_HEADER_RETURN_PATH;
	private $SMTP_HEADER_XMAILER;
	private $SMTP_HEADER_MESSAGEID;
	//
	//TODO: the following two variables are not used but they should be
	//private $maxLimitItemsPerLoad = 500;//let's not load more than 500 items
	//private $zeroPriorityItemsMaxTimeInQueue = 300;//after this number of seconds any zero priority item will be put into PRI:1
	//
	private $purgeItemsAfter_state1 = 259200;//BY DEFAULT SENT MAILS WILL BE PURGED AFTER: 3 days(3*86400) - PARAM(mq_purge_sent_items_after_days)
	private $purgeItemsAfter_state2 = 604800;//BY DEFAULT FAILED MAILS WILL BE PURGED AFTER: 7 days(7*86400) - PARAM(mq_purge_unsent_items_after_days)
	//
	//
	private $sentMailCount = 0;
	private $error = array();

	/**
	 * Constructor
	 */
	function __construct() {
		$this->setup();
		$this->updateMailQueueState();
	}

	private function setup() {
		global $AZMAILER;
		$componentXmlData = $AZMAILER->getInstallXmlData();

		//Set start timer so we can bail out on too long execution (so to avoid script timeout)
		$this->AZNLMQM_startRuntime = AZMailerDateHelper::now();

		//SETUP - QUEUE MAX RUNTIME (default: 30 seconds)
		if ($AZMAILER->getOption("mq_max_runtime_sec")) {
			$this->AZNLMQM_maxRuntime = ((int)$AZMAILER->getOption("mq_max_runtime_sec"));
		}

		set_time_limit($this->AZNLMQM_maxRuntime + $this->AZNLMQM_runtimeMargin);//try to set it to user configured value PLUS some margin
		$ini_time_limit = ini_get('max_execution_time');
		if ($ini_time_limit < ($this->AZNLMQM_maxRuntime + $this->AZNLMQM_runtimeMargin)) {//should be equal
			//time limit has NOT been changed so we need to lower AZNLMQM_maxRuntime
			$this->AZNLMQM_maxRuntime = $ini_time_limit - $this->AZNLMQM_runtimeMargin;
		}

		//SETUP - PURGE PERIODS
		if ($AZMAILER->getOption("mq_purge_sent_items_after_days")) {
			$this->purgeItemsAfter_state1 = ((int)$AZMAILER->getOption("mq_purge_sent_items_after_days")) * 24 * 60 * 60;
		}
		if ($AZMAILER->getOption("mq_purge_unsent_items_after_days")) {
			$this->purgeItemsAfter_state2 = ((int)$AZMAILER->getOption("mq_purge_unsent_items_after_days")) * 24 * 60 * 60;
		}

		//SETUP - HELO
		$this->HELO = $AZMAILER->getOption('mail_default_helo');
		if (empty($this->HELO)) {
			$this->HELO = 'localhost';
		}

		//SETUP - SMTP X-MAILER
		$this->SMTP_HEADER_XMAILER = $AZMAILER->getOption('smtp_header_x_mailer');
		if (empty($this->SMTP_HEADER_XMAILER)) {
			$this->SMTP_HEADER_XMAILER = $componentXmlData["name"] . ' v.' . $componentXmlData["version"];
		}

		//SETUP - SMTP RETURN-PATH (BOUNCE ADDRESS)
		$this->SMTP_HEADER_RETURN_PATH = $AZMAILER->getOption('smtp_header_return_path');
		if (empty($this->SMTP_HEADER_RETURN_PATH)) {
			$this->SMTP_HEADER_RETURN_PATH = null;
		}//sender address will be used

		//SETUP - SMTP MESSAGE ID AT (Message-ID: [uniqueID][@something])
		$this->SMTP_HEADER_MESSAGEID = $AZMAILER->getOption('smtp_header_message_id_at');
		if (empty($this->SMTP_HEADER_MESSAGEID)) {
			$this->SMTP_HEADER_MESSAGEID = '@' . $this->HELO;
		}
		if ($this->SMTP_HEADER_MESSAGEID[0] != '@') {
			$this->SMTP_HEADER_MESSAGEID = '@' . $this->SMTP_HEADER_MESSAGEID;
		}


		//SETUP - QUEUE MAX BLOCK TIME
		if ($AZMAILER->getOption("mq_max_blocked_time_sec")) {
			$this->maxQueueBlockedTime = ((int)$AZMAILER->getOption("mq_max_blocked_time_sec"));
		}


		//SETUP - PRIORITY ZERO
		$this->priorities[0] = new \stdClass();
		$this->priorities[0]->sendAfterLastAttempt = 0;//right away
		$this->priorities[0]->attemptsBeforeHigheringPriority = 1;

		//SETUP - PRIORITIES 1-5
		for ($i = 1; $i <= 5; $i++) {
			//number of send attempts after which item is moved to higher PRI
			$attempt_num = (int)($AZMAILER->getOption('mq_pri' . $i . '_attempts_num') ? $AZMAILER->getOption('mq_pri' . $i . '_attempts_num') : 1);
			//time to wait in current state before retry
			$attempt_delay = (int)($AZMAILER->getOption('mq_pri' . $i . '_attempts_delay_sec') ? $AZMAILER->getOption('mq_pri' . $i . '_attempts_delay_sec') : ($i * 600));
			$this->priorities[$i] = new \stdClass();
			$this->priorities[$i]->sendAfterLastAttempt = $attempt_delay;
			$this->priorities[$i]->attemptsBeforeHigheringPriority = $attempt_num;
		}

		$this->error = array();
		$this->error["number"] = 0;
		$this->error["type"] = "info";
		$this->error["message"] = 'OK';
	}

	/**
	 * @param boolean|null $blocked
	 * @return boolean
	 */
	private function updateMailQueueState($blocked = null) {
		if ($this->checkIfQueueIsEnabled()) {
			$MQS = \JTable::getInstance('azmailer_mail_queue_state', 'Table');
			$MQS->load($this->MQID);
			$data["id"] = $this->MQID;
			if ($blocked === true || $blocked === false) {
				$data["blocked"] = $blocked;
				$data["blocked_date"] = ($blocked === true ? AZMailerDateHelper::now() : 0);
			}
			$data["sent_count"] = (int)$MQS->sent_count + $this->sentMailCount;
			$data["last_updated_date"] = AZMailerDateHelper::now();
			$data["unsent_count"] = AZMailerQueuemanagerHelper::countUnsentMails();
			if ($MQS->save($data)) {
				$this->sentMailCount = 0;
			} else {
				$this->error["number"] = 12;
				$this->error["type"] = "error";
				$this->error["message"] = 'Unable to change Queue state!';
				return (false);
			}
		}
		return (true);
	}

	/**
	 * @return bool
	 */
	private function checkIfQueueIsEnabled() {
		$MQS = \JTable::getInstance('azmailer_mail_queue_state', 'Table');
		$MQS->load($this->MQID);
		if ($MQS->enabled == 1) {
			return (true);
		} else {
			$this->error["number"] = 11;
			$this->error["type"] = "info";
			$this->error["message"] = 'Queue is not enabled. Exiting.';
			return (false);
		}
	}

	/**
	 * @return array
	 */
	public function getError() {
		return ($this->error);
	}


	//--------------------------------------------------------------------------------PRIVATE METHODS
	/**
	 * @return integer
	 */
	public function getSentMailCount() {
		return ($this->sentMailCount);
	}

	/**
	 * Call this function if you want to send a mail queue item right away ONLY PRI === 0
	 * DO NOT USE THIS FOR SENDING MAILS IN HIGHER PRIORITY
	 * THIS DOES NOT CHECK/BLOCK MAIL QUEUE
	 * @param integer $MQIID
	 * @return boolean
	 */
	public function sendZeroPriorityMail($MQIID) {
		$MQI = $this->getSingleMailQueueItem($MQIID);
		$MSRES = $this->sendSingleMail($MQI);
		$this->updateMailQueueState();
		return ($MSRES);
	}

	/**
	 * @param integer $MQIID
	 * @return AZMailerQueueItem
	 */
	private function getSingleMailQueueItem($MQIID) {
		$MQI = \JTable::getInstance('azmailer_mail_queue_item', 'Table');
		$MQI->load($MQIID);
		$MQI = new AZMailerQueueItem($MQI);
		return ($MQI);
	}

	/**
	 * @param AZMailerQueueItem $MQI
	 * @return bool
	 */
	private function sendSingleMail($MQI) {
		$RES_CODE = 999;
		$RES_LOG = array();
		try {
			//CREATE MAIL
			$MS = new \stdClass();
			$MS->helo = $this->HELO;
			$MS->from = $MQI->get("mq_from");
			$MS->fromname = $MQI->get("mq_from_name");
			$MS->to = $MQI->get("mq_to");
			$MS->subject = $MQI->get("mq_subject");
			$MS->returnpath = $this->SMTP_HEADER_RETURN_PATH;
			$MS->xmailer = $this->SMTP_HEADER_XMAILER;
			$MS->messageid = $this->SMTP_HEADER_MESSAGEID;
			$MS->text = $MQI->getSubstitutedBody("text");
			$MS->html = $MQI->getSubstitutedBody("html");
			//$MS->headers = array();//-custom headers
			$POSTMAN = new AZMailerPostman($MS);
			//
			$this->elaborateHtmlBody($POSTMAN, $MQI);
			//SEND
			$IS_SENT = $POSTMAN->sendMail();
			$RES_CODE = $POSTMAN->getResultCode();
			$RES_LOG = $POSTMAN->getLogs();
		} catch (\Exception $e) {
			$IS_SENT = false;
			$RES_LOG[] = "Unknown error while sending mail: " . $e->getMessage();
		}
		$this->registerMailQueueItemResult($MQI, $IS_SENT, $RES_CODE, $RES_LOG);
		return ($IS_SENT);
	}

	/**
	 * @param AZMailerPostman   $POSTMAN
	 * @param AZMailerQueueItem $MQI
	 * @return mixed
	 */
	private function elaborateHtmlBody($POSTMAN, $MQI) {
		$this->elaborateHtmlBody___addAttachments($POSTMAN, $MQI);
		$this->elaborateHtmlBody___embedImages($POSTMAN, $MQI);
		$this->elaborateHtmlBody___addReadConfirmationUrl($POSTMAN, $MQI);
		$this->elaborateHtmlBody___addGoogleFonts($POSTMAN, $MQI);
	}

	/**
	 * @param AZMailerPostman   $POSTMAN
	 * @param AZMailerQueueItem $MQI
	 */
	private function elaborateHtmlBody___addAttachments($POSTMAN, $MQI) {
		global $AZMAILER;
		$html = $POSTMAN->getMailData("html");
		$text = $POSTMAN->getMailData("text");
		$attHtml = '';
		$attText = '';
		$ATTACHMENTS = json_decode(base64_decode($MQI->get("mq_attachments")));
		$MAXFILESIZE = (int)$AZMAILER->getOption("nl_max_filesize_to_attach_mb");
		$MAXFILESIZE = $MAXFILESIZE * 1024 * 1024;//bytes
		$attachmentFolder = $AZMAILER->getOption("newsletter_attachment_base");
		if (is_object($ATTACHMENTS) && isset($ATTACHMENTS->attachments) && is_array($ATTACHMENTS->attachments) && count($ATTACHMENTS->attachments)) {
			foreach ($ATTACHMENTS->attachments as &$attachment) {
				if ($attachment->size > $MAXFILESIZE) {
					$downloadUrl = AZMailerNewsletterHelper::getDownloadUrlForAttachment($MQI->get("mq_typeid"), $attachment, $MQI->get("mq_to"));
					$attHtml .= '<a href="' . $downloadUrl . '">' . $attachment->name . '</a>';
					$attHtml .= '<br />';
					$attText .= $attachment->name . ': ' . $downloadUrl . "\n";
				} else {
					$FULLPATH = JPATH_ROOT . DS . $attachmentFolder . DS . $attachment->filename;
					$POSTMAN->add_attachment($FULLPATH, $attachment->name, "attachment");
				}
			}
		}
		if (preg_match('/\{NEWSLETTERATTACHMENTS\}/', $html)) {
			$html = preg_replace('/\{NEWSLETTERATTACHMENTS\}/', $attHtml, $html);
		} else {
			$html = $html . $attHtml;
		}
		$POSTMAN->set_html($html);

		if (preg_match('/\{NEWSLETTERATTACHMENTS\}/', $text)) {
			$text = preg_replace('/\{NEWSLETTERATTACHMENTS\}/', $attText, $text);
		} else {
			$text = $text . $attText;
		}
		$POSTMAN->set_text($text);
	}

	/**
	 * @param AZMailerPostman   $POSTMAN
	 * @param AZMailerQueueItem $MQI
	 */
	private function elaborateHtmlBody___embedImages($POSTMAN, $MQI) {
		/** @var \AZMailer\AZMailerCore $AZMAILER */
		global $AZMAILER;
		$html = $POSTMAN->getMailData("html");
		/**
		 * Since image paths in html already have Joomla deployment subfolder(if any) in front, we need to remove it from JPATH_ROOT
		 * So we get something like /var/www/vhost even if joomla is installed in /var/www/vhost/joomla_subfolder.
		 */
		$pathToVhost = str_replace($AZMAILER->getOption('j_deploy_folder'), '', JPATH_ROOT);
		if (!$html) return;
		$regex_all_tags = '#<[^/][^>]+(?=background-image|src)[^>]+(?=\.(jpe?g|png|gif)[\"\'])[^>]*>#i';//only opening rags
		preg_match_all($regex_all_tags, $html, $TAGS);
		if (is_array($TAGS) && isset($TAGS[0]) && is_array($TAGS[0])) {
			$TAGS = $TAGS[0];
			//print_r($TAGS);
			foreach ($TAGS as $TAG) {
				if (preg_match('#background-image#i', $TAG)) {
					//echo "BG: $TAG \n";
					$regex_img_url = '#background-image:[^(]*\([\"\']([^\"\']*)[\"\']#i';
					preg_match_all($regex_img_url, $TAG, $IMGURL);
					if (isset($IMGURL[1][0])) {
						$IMGURL = $IMGURL[1][0];
						$FULLIMGPATH = $pathToVhost . (substr($IMGURL, 0, 1) != "/" ? "/" : "") . $IMGURL;
						if (file_exists($FULLIMGPATH)) {
							$IMGINFO = pathinfo($FULLIMGPATH);
							$uniqueID = AZMailerMimeHelper::unique();
							$IMGNAME = $IMGINFO["filename"] . $uniqueID . '.' . $IMGINFO["extension"];
							$ATT = $POSTMAN->add_attachment($FULLIMGPATH, $IMGNAME, 'inline', $uniqueID);
							if ($ATT) {
								$CIDRES = 'cid:' . $uniqueID;
								$MODIMGTAG = preg_replace('# ?background-image:[^(]*\([\"\']([^\"\']*)[\"\']\);?#i', '', $TAG);//completely remove background-image style attribute
								$MODIMGTAG = str_replace('>', ' background="' . $CIDRES . '">', $MODIMGTAG);
								$html = str_replace($TAG, $MODIMGTAG, $html);
							}
						}
					}
				} else if (preg_match('#<img[^>]+(?=src)#i', $TAG)) {
					//echo "IMG: $TAG \n\n";
					$regex_img_url = '#src=[\"\']([^\"\']*)[\"\']#i';
					preg_match_all($regex_img_url, $TAG, $IMGURL);
					if (isset($IMGURL[1][0])) {
						$IMGURL = $IMGURL[1][0];
						$FULLIMGPATH = $pathToVhost . (substr($IMGURL, 0, 1) != "/" ? "/" : "") . $IMGURL;
						if (file_exists($FULLIMGPATH)) {
							$IMGINFO = pathinfo($FULLIMGPATH);
							$uniqueID = AZMailerMimeHelper::unique();
							$IMGNAME = $IMGINFO["filename"] . $uniqueID . '.' . $IMGINFO["extension"];
							$ATT = $POSTMAN->add_attachment($FULLIMGPATH, $IMGNAME, 'inline', $uniqueID);
							if ($ATT) {
								$CIDRES = 'cid:' . $uniqueID;
								$MODIMGTAG = preg_replace('#src=[\"\'][^\"\']*[\"\']#i', 'src="' . $CIDRES . '"', $TAG);
								$html = str_replace($TAG, $MODIMGTAG, $html);
							}
						}
					}
				}
			}
		}
		$POSTMAN->set_html($html);
	}

	/**
	 * @param AZMailerPostman   $POSTMAN
	 * @param AZMailerQueueItem $MQI
	 */
	private function elaborateHtmlBody___addReadConfirmationUrl($POSTMAN, $MQI) {
		global $AZMAILER;
		$html = $POSTMAN->getMailData("html");
		if (!$html) return;
		$CTRL = urlencode(base64_encode($MQI->get("id")));
		$CTRLSTR = strtolower($MQI->get("mq_to") . '#' . $MQI->get("mq_date") . '#' . $MQI->get("mq_type") . '#' . $MQI->get("mq_typeid"));
		$CTRL .= ':' . sha1(md5($CTRLSTR));
		$LNK = '' . $AZMAILER->getOption('newsletter_http_host')
			. '/index.php?option=' . $AZMAILER->getOption('com_name')
			. '&task=azmailer.domqirc'
			. '&format=raw'
			. '&ctrl=' . $CTRL;
		$RCU = '<img src="' . $LNK . '" style="display:none;visibility:hidden; width:0; height:0;" />';
		$html = $html . $RCU;
		$POSTMAN->set_html($html);
	}

	/**
	 * @param AZMailerQueueItem $MQI
	 * @param boolean           $IS_SENT
	 * @param int               $RES_CODE
	 * @param array             $RES_LOG
	 */
	private function registerMailQueueItemResult($MQI, $IS_SENT, $RES_CODE, $RES_LOG) {
		//fwrite(STDOUT, "SENDRES{".$MQI->get("id")."}($RES_CODE): " . json_encode($RES_LOG)."\n");

		/** @var \JTable $table */
		$table = \JTable::getInstance('azmailer_mail_queue_item', 'Table');
		$data = array();
		$data["id"] = $MQI->get("id");
		$data["mq_send_attempt_count"] = $MQI->get("mq_send_attempt_count") + 1;
		$data["mq_last_send_attempt_date"] = AZMailerDateHelper::now();
		$data["mq_last_send_attempt_result_code"] = $RES_CODE;
		$data["mq_last_send_attempt_log"] = json_encode($RES_LOG);
		if (!$IS_SENT) { //for unsent items we will increase priority (only Up to 5) according to $priorities settings
			if ($data["mq_send_attempt_count"] >= $this->countMaxAttemptsForPriorityLevel($MQI->get("mq_priority"))) {
				if (($MQI->get("mq_priority") + 1) <= 5) {
					$data["mq_priority"] = $MQI->get("mq_priority") + 1;
				} else {
					//message has a permanent failure - failed send in all priorities
					$data["mq_state"] = AZMailerQueueItem::$STATE_FAILED;//FAILED
					//TODO: - it would be a good idea to register on subscriber this permanent failure (with the LOGS)
					//so we know that the subscriber is doggy
				}
			}
			//MQIs will have only one chance to be sent right away in PRI=0 - if fails thew will follow regular queue behaviour
			if ($MQI->get("mq_priority") == 0) {
				$data["mq_priority"] = 1;
			}
		} else {
			$data["mq_state"] = 1;//SENT
			//TODO: make this optional if we want log or not on successfully sent items
			//$data["mq_last_send_attempt_log"] = "";
			$this->sentMailCount++;
		}
		$table->save($data);
	}

	/**
	 * @param int $p
	 * @return int
	 */
	private function countMaxAttemptsForPriorityLevel($p = 5) {
		$answer = 0;
		for ($i = 0; $i <= $p; $i++) {
			$answer += $this->priorities[$i]->attemptsBeforeHigheringPriority;
		}
		return ($answer);
	}

	/**
	 * @return bool
	 */
	public function workQueue() {
		if ($this->checkIfQueueIsEnabled()) {
			if ($this->checkIfQueueIsUsable()) {
				if ($this->updateMailQueueState(1)) {
					$this->sendMails();
					$this->purgeQueueItems();
					if ($this->updateMailQueueState(0)) {
						return (true);
					}
				}
			}
		}
		return (false);
	}

	/**
	 * @return bool
	 */
	private function checkIfQueueIsUsable() {
		$MQS = \JTable::getInstance('azmailer_mail_queue_state', 'Table');
		$MQS->load($this->MQID);
		if ($MQS->blocked == 1) {//MAIL QUEUE IS BLOCKED
			//if ($MQS->blocked_date < (AZMailerDateHelper::now() - $this->maxQueueBlockedTime)) {
			if ((AZMailerDateHelper::now() - $this->maxQueueBlockedTime) < $MQS->blocked_date) {
				//MAIL QUEUE MUST REMAIN BLOCKED UNTIL maxQueueBlockedTime expires
				$this->error["number"] = 10;
				$this->error["type"] = "error";
				$this->error["message"] = 'Queue is still blocked by another process.';
				return (false);
			} else {
				$this->updateMailQueueState(0);//unblock queue
			}
		}
		return (true);
	}

	/**
	 * Send as many mails as possible
	 * @return bool
	 */
	private function sendMails() {
		$timeIsUp = false;
		for ($pri = 1; $pri <= 5; $pri++) {
			$MQIA = $this->getItemsToBeSentForPriority($pri);
			if (!count($MQIA)) continue;
			foreach ($MQIA as $MQIID) {
				$MQI = $this->getSingleMailQueueItem($MQIID);
				$this->sendSingleMail($MQI);
				$this->updateMailQueueState();
				$timeIsUp = $this->checkIfTimeIsUp();
				if ($timeIsUp) {
					break;
				}
			}
			if ($timeIsUp) {
				break;
			}
		}
		return (true);
	}

	/**
	 * returns array of ids of MQIs to be sent in selected priority level
	 * TODO: use $maxLimitItemsPerLoad to limit items to a reasonable size
	 * @param $priorityLevel (1 to 5)
	 * @return mixed
	 */
	private function getItemsToBeSentForPriority($priorityLevel) {
		$db = \JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.id');
		$query->from('#__azmailer_mail_queue_item AS a');
		$query->where('a.mq_state = 0');
		$query->where('a.mq_priority = ' . $db->quote($priorityLevel));
		$query->where('a.mq_last_send_attempt_date <= ' . (AZMailerDateHelper::now() - $this->priorities[$priorityLevel]->sendAfterLastAttempt));
		$query->order('a.mq_last_send_attempt_date');
		$db->setQuery($query);
		return ($db->loadColumn());
	}

	/**
	 * @return bool
	 */
	private function checkIfTimeIsUp() {
		return (($this->AZNLMQM_startRuntime + $this->AZNLMQM_maxRuntime) < AZMailerDateHelper::now());
	}

	private function purgeQueueItems() {
		$db = \JFactory::getDbo();
		//STATE 1 ITEMS - SENT
		$where = array();
		$where[] = 'mq_state = 1';
		$where[] = 'mq_last_send_attempt_date <= ' . (AZMailerDateHelper::now() - $this->purgeItemsAfter_state1);
		$where = count($where) ? ' WHERE ' . implode(' AND ', $where) : '';
		$sql = 'DELETE FROM #__azmailer_mail_queue_item' . $where;
		$db->setQuery($sql);
		$db->execute();
		//STATE 2 ITEMS - FAILED
		$where = array();
		$where[] = 'mq_state = 2';
		$where[] = 'mq_last_send_attempt_date <= ' . (AZMailerDateHelper::now() - $this->purgeItemsAfter_state2);
		$where = count($where) ? ' WHERE ' . implode(' AND ', $where) : '';
		$sql = 'DELETE FROM #__azmailer_mail_queue_item' . $where;
		$db->setQuery($sql);
		$db->execute();
	}

	/**
	 * @param AZMailerPostman   $POSTMAN
	 * @param AZMailerQueueItem $MQI
	 */
	private function elaborateHtmlBody___addGoogleFonts($POSTMAN, $MQI) {
		//$html = $POSTMAN->getMailData("html");
		//if (!$html) return;
		//$fontStyleInclusion = '<style type="text/css">@import url(http://fonts.googleapis.com/css?family=Ovo);</style>';
		//$fontStyleInclusion = '<link href="http://fonts.googleapis.com/css?family=Ovo" rel="stylesheet" type="text/css">';
		//$html = $fontStyleInclusion . $html;
		//$POSTMAN->set_html($html);
	}

}