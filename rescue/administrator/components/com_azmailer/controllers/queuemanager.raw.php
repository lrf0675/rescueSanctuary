<?php
/**
 * @package    AZMailer
 * @author     Adam Jakab {@link http://www.alfazeta.com}
 * @author     Created on 09-Feb-2013
 * @license    GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
use AZMailer\Core\AZMailerController;
use AZMailer\Entities\AZMailerQueueItem;
use AZMailer\Helpers\AZMailerQueuemanagerHelper;

/**
 * Controller for QueueManager - RAW REQUESTS
 * contoller is called with "format=raw"
 * No View is involved - works with model and outputs clean data
 */
class AZMailerControllerQueuemanager extends AZMailerController {
	/** @var $model \AZMailerModelQueuemanager */
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

	public function previewQueueItem() {
		\JFactory::getDocument()->setMimeEncoding('text/html');
		$JI = \JFactory::getApplication()->input;
		$mqiid = $JI->getInt("mqiid", null);
		$MQI = new AZMailerQueueItem(AZMailerQueuemanagerHelper::getMQIById($mqiid));
		$answer = $MQI->getSubstitutedBody("html");
		echo $answer;
	}

	public function getQueueItemLogs() {
		\JFactory::getDocument()->setMimeEncoding('text/plain');
		$JI = \JFactory::getApplication()->input;
		$mqiid = $JI->getInt("mqiid", null);
		$MQI = new AZMailerQueueItem(AZMailerQueuemanagerHelper::getMQIById($mqiid));
		echo $MQI->get("mq_last_send_attempt_log");
	}

	public function enableMailQueue() {
		$answer = new \stdClass();
		$answer->result = AZMailerQueuemanagerHelper::setMailQueue(1);
		$answer->errors = array();
		echo json_encode($answer);
	}

	public function disableMailQueue() {
		$answer = new \stdClass();
		$answer->result = AZMailerQueuemanagerHelper::setMailQueue(0);
		$answer->errors = array();
		echo json_encode($answer);
	}

}
