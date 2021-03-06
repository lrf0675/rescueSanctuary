<?php
/**
 * @package    AZMailer
 * @author     Adam Jakab {@link http://www.alfazeta.com}
 * @author     Created on 09-Feb-2013
 * @license    GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.controller');

/**
 * AZMailer Front-end Controller
 */
class AZMailerControllerAZMailer extends \JControllerLegacy {
	/**
	 * @param array $config
	 */
	function __construct($config = array()) {
		parent::__construct($config);
	}

	/**
	 * @param bool $cachable
	 * @param bool $urlparams
	 * @return JControllerLegacy|void
	 */
	public function display($cachable = false, $urlparams = false) {
		global $AZMAILER;
		$view = $this->getView($AZMAILER->getOption("controller"), 'html', '');
		$JI = \JFactory::getApplication()->input;
		$tmpl = $JI->getString("tmpl", "default");
		if ( ($model = $this->getModel($AZMAILER->getOption("controller"))) ) {
			/** @var \JModelLegacy $model */
			$view->setModel($model, true);
		}
		$view->setLayout($tmpl);
		if (method_exists($view, $AZMAILER->getOption('task'))) {
			call_user_func(array($view, $AZMAILER->getOption('task')));
		} else {
			$view->display();
		}
	}

}
