<?php
/**
 * @package    AZMailer
 * @author     Adam Jakab {@link http://www.alfazeta.com}
 * @author     Created on 09-Feb-2013
 * @license    GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
use AZMailer\Core\AZMailerController;


/**
 * Controller for Category - AJAX REQUESTS
 * contoller is called with "format=raw"
 * No View is involved - works with model and outputs clean data in JSON FORMAT
 *
 */
class AZMailerControllerSettings extends AZMailerController {
	/** @var AZMailerModelSettings */
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
		$answer = new stdClass();
		$answer->result = false;
		$answer->errors[] = "The task you have requested does not exist!\nTask: " . $AZMAILER->getOption("ctrl.task");
		echo json_encode($answer);
	}


	public function getParamEditForm() {
		$JI = \JFactory::getApplication()->input;
		$paramName = $JI->getString("paramName", null);
		$answer = new stdClass();
		$answer->result = $this->model->getParamEditForm($paramName);
		$answer->errors = array();
		echo json_encode($answer);
	}

	public function submitParamEditForm() {
		$JI = \JFactory::getApplication()->input;
		$paramName = $JI->getString("paramName", null);
		$paramValue = $JI->getString("paramValue", null);
		$answer = new stdClass();
		$answer->result = $this->model->submitParamEditForm($paramName, $paramValue);
		$answer->errors = array();
		echo json_encode($answer);
	}

}
