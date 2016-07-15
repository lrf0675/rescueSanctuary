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
class AZMailerControllerCategory extends AZMailerController {
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
		$cat_index = $JI->getInt("cat_index");
		$name = $JI->getString("name");
		$answer = new stdClass();
		$answer->result = $this->model->addNew($cat_index, $name);
		$answer->errors = $this->model->getErrors();
		echo json_encode($answer);
	}

	public function changeName() {
		$JI = JFactory::getApplication()->input;
		$cat_index = $JI->getInt("cat_index");
		$name = $JI->getString("name");
		$id = $JI->getInt("id");
		$answer = new stdClass();
		$answer->result = $this->model->changeName($cat_index, $name, $id);
		$answer->errors = $this->model->getErrors();
		echo json_encode($answer);
	}

	public function delete() {
		$JI = JFactory::getApplication()->input;
		$cat_index = $JI->getInt("cat_index");
		$id = $JI->getInt("id");
		$answer = new stdClass();
		$answer->result = $this->model->delete($cat_index, $id);
		$answer->errors = $this->model->getErrors();
		echo json_encode($answer);
	}

	public function setDefaultOption() {
		$JI = JFactory::getApplication()->input;
		$cat_index = $JI->getInt("cat_index");
		$id = $JI->getInt("id");
		$is_default = $JI->getInt("is_default", 0);
		$answer = new stdClass();
		$answer->result = $this->model->setDefaultOption($cat_index, $id, $is_default);
		$answer->errors = $this->model->getErrors();
		echo json_encode($answer);
	}

	public function saveOrderedItems() {
		$JI = JFactory::getApplication()->input;
		$cat_index = $JI->getInt("cat_index");
		$serialized = $JI->getString("serialized");
		$answer = new stdClass();
		$answer->result = $this->model->saveOrderedItems($cat_index, $serialized);
		$answer->errors = $this->model->getErrors();
		echo json_encode($answer);
	}

}
