<?php
/**
 * @package    AZMailer
 * @author     Adam Jakab {@link http://www.alfazeta.com}
 * @author     Created on 09-Feb-2013
 * @license    GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
use AZMailer\Core\AZMailerController;
use AZMailer\Helpers\AZMailerDBUpdaterHelper;

/**
 * Controller for Settings
 * Extends AZMailerController which has the default display function
 * which will handle all actions in absence of exlicit handler function.
 */
class AZMailerControllerSettings extends AZMailerController {
	/**
	 * @param array $config
	 */
	function __construct($config = array()) {
		parent::__construct($config);
	}

	function checkAndUpdateAZMailerTables() {
		//global $AZMAILER;
		$AZMDBUH = new AZMailerDBUpdaterHelper();
		$AZMDBUH->update(true); //true is for verbose
		$this->display();
	}

}
