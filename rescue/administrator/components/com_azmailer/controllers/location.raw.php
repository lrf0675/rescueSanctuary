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
 * Controller for Location - AJAX REQUESTS
 * contoller is called with "format=raw"
 * No View is involved - works with model and outputs clean data in JSON FORMAT
 *
 */
class AZMailerControllerLocation extends AZMailerController {
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


	public function addNew() {
		$JI = JFactory::getApplication()->input;
		$add_what = $JI->getString('add_what');
		$name = $JI->getString('name');
		$sigla = strtoupper($JI->getString('sigla',''));
		$country_id = $JI->getInt('country_id');
		$region_id = $JI->getInt('region_id');
		$answer = new stdClass();
		$answer->result = $this->model->addNew($add_what, $name, $sigla, $country_id, $region_id);
		$answer->errors = $this->model->getErrors();
		echo json_encode($answer);
	}

	public function changeName() {
		$JI = JFactory::getApplication()->input;
		$change_what = $JI->getString('change_what');
		$name = $JI->getString('name');
		$sigla = strtoupper($JI->getString('sigla', ''));
		$id = $JI->getInt('id');
		$answer = new stdClass();
		$answer->result = $this->model->changeName($change_what, $name, $sigla, $id);
		$answer->errors = $this->model->getErrors();
		echo json_encode($answer);
	}

	public function delete() {
		$JI = JFactory::getApplication()->input;
		$delete_what = $JI->getString('delete_what');
		$id = $JI->getInt('id');
		$answer = new stdClass();
		$answer->result = $this->model->delete($delete_what, $id);
		$answer->errors = $this->model->getErrors();
		echo json_encode($answer);
	}
}
