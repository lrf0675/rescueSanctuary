<?php
namespace AZMailer;
/**
 * @package    AZMailer
 * @author     Adam Jakab {@link http://www.alfazeta.com}
 * @author     Created on 09-Feb-2013
 * @license    GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
use AZMailer\Core\AZMailerException;
use AZMailer\Helpers\AZMailerAdminInterfaceHelper;
use AZMailer\Helpers\AZMailerComponentParamHelper;

if (!class_exists('AZMailerCore')) {
	jimport('joomla.filesystem.file');
	jimport('joomla.application.component.controller');

	/**
	 * Class AZMailerCore
	 * @package AZMailer
	 */
	class AZMailerCore {
		private $options = array();
		private $parametersSetupArray = null;
		private $_ctrl = null;

		/**
		 * Main AZMailer Application
		 */
		public function __construct() {
			//register as global
			$GLOBALS['AZMAILER'] = &$this;

			//get component parameters setup array
			$this->parametersSetupArray = AZMailerComponentParamHelper::getParametersSetupArray();

			//
			$this->setOption('com_path_admin', JPATH_COMPONENT_ADMINISTRATOR);
			$this->setOption('com_path_user', JPATH_COMPONENT_SITE);

			//set component name
			$this->setOption('com_name', 'com_azmailer');

			//we only need this in web environment and will throw warning when AZMailer is called by cli
			if (php_sapi_name() != 'cli') {
				$j_uri = \JUri::getInstance();

				/**
				 * The base path: [deployFolder]/(""|"administrator")
				 * @var string $uriBase
				 */
				$uriBase = $j_uri->base(true);

				/**
				 * The path to the subfolder where joomla is deployed
				 */
				$deployFolder = str_replace('/administrator', '', $uriBase);
				$this->setOption('j_deploy_folder', $deployFolder);

				/**
				 * The front-end path to the component folder: [deployFolder]/components/com_azmailer
				 */
				$this->setOption('com_uri', $deployFolder . '/components/' . $this->getOption('com_name'));

				/**
				 * The back-end path to the component folder: [deployFolder]/administrator/components/com_azmailer
				 */
				$this->setOption('com_uri_admin', $deployFolder . '/administrator/components/' . $this->getOption('com_name'));
			}


			//-CONTROLLER/TASK/VIEW
			$JI = new \JInput;
			$ctrl = 'cp';
			$task = $JI->getCmd('task', 'display');
			$format = $JI->getCmd('format', '');
			if ($task && strpos($task, '.') !== false) {
				$ctrl = preg_replace('#\..*#', '', $task);
				$task = preg_replace('#.*\.#', '', $task);
			}
			$view = $ctrl;
			$JI->set("task", $ctrl . '.' . $task);
			$JI->set("view", $view);
			$this->setOption('ctrl.task', $ctrl . '.' . $task);
			$this->setOption('controller', $ctrl);
			$this->setOption('task', $task);
			$this->setOption('view', $view);
			$this->setOption('format', $format);
		}

		/**
		 * @param null $componentLocation : 'frontend' or 'backend'
		 */
		public function init($componentLocation = null) {
			//CHECK CORE COMPONENT ACCESS

			//echo JPATH_ROOT;

			//TODO: define user permissions!!!
			if (!\JFactory::getUser()->authorise('core.manage', 'com_azmailer')) {
				//throw new \Exception(JText::_('COM_AZMAILER_AUTH_NO'), 404);
				//die(\JText::_('COM_AZMAILER_AUTH_NO'));
			}

			$componentLocation = (in_array($componentLocation, array('frontend', 'backend')) ? $componentLocation : 'frontend');
			$this->setOption('com_location', $componentLocation);


			if ($this->getOption('format') != 'raw') {
				AZMailerAdminInterfaceHelper::setHeaderIncludes();
			}

			//Run the controller and manage extension specific exceptions
			try {
				$controller = \JControllerLegacy::getInstance('AZMailer');
				if ($controller) {
					$this->_ctrl = $controller;
					$controller->execute($this->getOption('task'));
					$controller->redirect();
				}
			} catch (AZMailerException $e) {
				\JToolBarHelper::title("AZMailerException");
				echo($e->getFormattedErrorMessage());
			}

		}

		/**
		 * Returns component's xml parsed into an array
		 * We need to use absolute path here because this function is also called by plugin
		 * when the current component is not "com_azmailer"
		 * like: XML: failed to load external entity ".../administrator/components/com_users/azmailer.xml"
		 * @return array
		 */
		public function getInstallXmlData() {
			$answer = array();
			$xmlFilePath = JPATH_ROOT . DS . "administrator" . DS . "components" . DS . "com_azmailer" . DS . "azmailer.xml";
			if (($data = \JInstaller::parseXMLInstallFile($xmlFilePath))) {
				$answer = $data;
			}
			return ($answer);
		}

		/**
		 * @return null|array
		 */
		public function getParametersSetupArray() {
			return ($this->parametersSetupArray);
		}

		/**
		 * Sets component local option or component parameter value(defined in $parametersSetupArray)
		 * @param string $name
		 * @param mixed  $value
		 * @param bool   $forceComponentParameter
		 */
		public function setOption($name, $value, $forceComponentParameter = false) {
			if ($this->getOption($name, true)) {
				AZMailerComponentParamHelper::setParamValue($name, $value);
			} else {
				if (!$forceComponentParameter) {
					$this->options[$name] = $value;
				}
			}
		}

		/** This will get local component option or component parameter value
		 * @param string $name
		 * @param bool   $forceComponentParameter
		 * @return bool|mixed|string
		 */
		public function getOption($name, $forceComponentParameter = false) {
			if (!$forceComponentParameter && array_key_exists($name, $this->options)) {
				$answer = $this->options[$name];
			} else {
				$answer = AZMailerComponentParamHelper::getParamValue($name);
			}
			return ($answer);
		}

		/**
		 * @return \JController|\JControllerLegacy
		 */
		public function getController() {
			return $this->_ctrl;
		}

	}
}
