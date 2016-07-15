<?php
namespace AZMailer\Cli\Model;

use AZMailer\Core\AZMailerQueueManager;
use AZMailer\Helpers\AZMailerDateHelper;
use Joomla\Registry\Registry;

defined('_JEXEC') or die('Restricted access');

/**
 * @package     AZMailer
 * @subpackage  Cli\Model
 */
class AZMailerCron {
	/** @var \JRegistry|Registry  */
	private $config;

	/** @var array */
	private $cronVars;

	/** @var array  */
	private $taskList = array();

	/** @var \stdClass  */
	private $currentTask = null;

	/**
	 * @param Registry $cliAppConf
	 */
	function __construct(Registry $cliAppConf = null) {
		$this->config = $cliAppConf;

		$this->cronVars = array();
		$this->cronVars["exec_time_start"] = date("U");
		$this->cronVars["exec_time_end"] = 0;
		$this->cronVars["messages"] = array();
		//
		$this->log("info", "-------------------------------------------------------------------------------------");
		$this->log("info", "--- EXECUTING CRON TASKS @ " . AZMailerDateHelper::convertToHumanReadableFormat($this->cronVars["exec_time_start"], "Y-m-d h:i:s"));
		$this->log("info", "-------------------------------------------------------------------------------------");
	}

	/**
	 * @param string $type
	 * @param string $msg
	 * @param bool $addToPrevious
	 */
	private function log($type, $msg, $addToPrevious = false) {
		if ($addToPrevious) {
			$msgObj = &$this->cronVars["messages"][count($this->cronVars["messages"])];
			$msgObj->msg .= $msg;
		} else {
			$msgObj = new \stdClass();
			$msgObj->ts = date("U");
			$msgObj->type = $type;
			$msgObj->msg = $msg;
			$this->cronVars["messages"][] = $msgObj;
		}

		if ($this->config->get('verbose')) {
			fwrite(STDOUT, AZMailerDateHelper::convertToHumanReadableFormat($msgObj->ts, "Y-m-d h:i:s") . "[" . $msgObj->type . "]: " . $msgObj->msg . "\n");
		}
	}

	public function executeTasks() {
		$this->taskList = $this->getCronTasksList();
		if ($this->taskList && is_array($this->taskList) && count($this->taskList)) {
			foreach ($this->taskList as $this->currentTask) {
				$this->executeCurrentTask();
			}
		}
		$this->log("info", "FINISHED");
		$this->cron_execution_terminate();
	}


	/**
	 * Get list of tasks to execute
	 * @return mixed
	 */
	private function getCronTasksList() {
		$db = \JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.*');
		$query->from('#__azmailer_crontask AS a');
		$query->where('a.enabled = 1');
		$db->setQuery($query);
		return ($db->loadObjectList());
	}

	//todo: this error_triggering business is v. stupid and inelegant - find a better way
	private function executeCurrentTask() {
		$errRepLevel = error_reporting();//save error reporting level
		error_reporting(0);
		trigger_error("Triggering custom error!", E_USER_NOTICE);//==1024
		error_reporting($errRepLevel);//restore
		$NOW = date("U");
		$LAST_EXECUTED = $this->currentTask->lastExecutionTime;
		$EIS = $this->currentTask->executionIntervalSeconds;
		$NEXT_EXECUTION = $LAST_EXECUTED + $EIS;
		if ($NEXT_EXECUTION <= $NOW) {
			$this->log("info", "Calling cron task: '" . $this->currentTask->cronTaskToCall /*. "' - " . json_encode($this->currentTask)*/);
			if (is_callable(array($this, $this->currentTask->cronTaskToCall), false)) {
				call_user_func(array($this, $this->currentTask->cronTaskToCall));
				$error = error_get_last();
				if ($error["type"] <> 1024) {
					$this->log("error", "THERE WAS AN ERROR[" . $this->currentTask->cronTaskToCall . "]: " . print_r($error, true));
					trigger_error("Triggering custom error!", E_USER_NOTICE);//==1024
				} else {
					$this->setCurrentTaskExecuted();
				}
			} else {
				$this->log("error", "THERE IS NO TASK BY THIS NAME: " . $this->currentTask->cronTaskToCall, false);
			}
		} else {
			$NEXT_EXECUTION_STR = AZMailerDateHelper::convertToHumanReadableFormat($NEXT_EXECUTION, "Y-m-d h:i:s");
			$this->log("info", "Task: '" . $this->currentTask->cronTaskToCall . "' skipped until $NEXT_EXECUTION_STR");
		}
	}

	private function setCurrentTaskExecuted() {
		$db = \JFactory::getDbo();
		$query = 'UPDATE #__azmailer_crontask SET lastExecutionTime = ' . date("U") . ' WHERE id = ' . $this->currentTask->id;
		$db->setQuery($query);
		$db->execute();
		$this->log("info", "Task execution completed. " /*. $query*/);
	}

	private function cron_execution_terminate() {
		$this->cronVars["exec_time_end"] = date("U");
		$this->cronVars["exectime"] = $this->cronVars["exec_time_end"] - $this->cronVars["exec_time_start"];
		$errors = 0;
		foreach ($this->cronVars["messages"] as &$msg) {
			if ($msg->type == "error") {
				$errors++;
			}
		}
		///FINAL MESSAGES
		$this->log("info", "-------------------------------------------------------------------------------------");
		$this->log("info", "--- FINISHED @ " . AZMailerDateHelper::convertToHumanReadableFormat($this->cronVars["exec_time_end"], "Y-m-d h:i:s"));
		$this->log("info", "--- TOTAL EXEC TIME(s) : " . $this->cronVars["exectime"]);
		$this->log("info", "--- TOTAL ERRORS : " . $errors);
		$this->log("info", "-------------------------------------------------------------------------------------");
	}

	protected function mailQueueCheck() {
		$this->log("info", "checking Mail Queue...");
		$AZMQM = new AZMailerQueueManager();
		if (!$AZMQM->workQueue()) {
			$E = $AZMQM->getError();
			$this->log($E["type"], "Mail Queue Manager says(" . $E["number"] . "): " . $E["message"]);
		}
	}
}
