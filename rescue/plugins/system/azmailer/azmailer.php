<?php
/**
 * @package    AZMailer
 * @subpackage Base
 * @author     Adam Jakab {@link http://www.alfazeta.com}
 * @author     Created on 13-May-2013
 * @license    GNU/GPL
 */
//-- No direct access
defined('_JEXEC') || die('=;)');
jimport('joomla.plugin.plugin');
jimport('joomla.error.log');

use \AZMailer\Entities\AZMailerQueueItem;


/**
 * System Plugin.
 *
 * @package    AZMailer
 * @subpackage Plugin
 */
class plgSystemAZMailer extends \JPlugin {
	/**
	 * Constructor
	 *
	 * @param object $subject The object to observe
	 * @param array  $config  An array that holds the plugin configuration
	 */
	public function __construct(& $subject, $config) {
		parent::__construct($subject, $config);
	}

	/**
		CALL CUSTOM FUNCTIONS IN THIS PLUGIN AS:
		$JAPP = \JFactory::getApplication();
	    $plgData = new \stdClass();
		$plgResp = $JAPP->triggerEvent("AZMSYSPLG_queueMail", array($plgData));
		$plgResp = $plgResp[0];
	*/

	/**
	 * Interface for queueing (and sending right away in zero priority) MQIs
	 * @param $MO
	 * @return bool|string
	 */
	public function AZMSYSPLG_queueMail($MO) {
		require_once(JPATH_ADMINISTRATOR.DS."components".DS."com_azmailer".DS."includes".DS."defines.php");
		require_once(JPATH_ADMINISTRATOR.DS."components".DS."com_azmailer".DS."includes".DS."loader.php");
		global $AZMAILER;
		if(!$AZMAILER){$AZMAILER = new \AZMailer\AZMailerCore();}
		if ($MO) {
			$AZMQI = new AZMailerQueueItem($MO);
			if ($AZMQI->setup()) {
				if ($AZMQI->enqueue()) {
					if ($AZMQI->send()) {
						$answer = true;
					} else {
						$answer = "MQI not sent!";
					}
				} else {
					$answer = "Mail Queue Item could not be inserted into queue!";
				}
			} else {
				$answer = "Mail Queue Item could not be set up!";
			}
		} else {
			$answer = "Mail Object is not defined!";
		}
		return($answer);
	}

	/**
	 * todo: unified subscriber registration method is missing here
	 * Interface for registering NLSs - should be the unified interface for NLS registration
	 * @param \stdClass $plgData
	 *
	 * $JAPP = \JFactory::getApplication();
	 * $plgData = new \stdClass();
	 * $plgResp = $JAPP->triggerEvent("AZMSYSPLG_registerSubscriber", array($plgData));
	 * $plgResp = $plgResp[0];
	 */
	public function AZMSYSPLG_registerSubscriber($plgData) {
		require_once(JPATH_ADMINISTRATOR.DS."components".DS."com_azmailer".DS."includes".DS."defines.php");
		require_once(JPATH_ADMINISTRATOR.DS."components".DS."com_azmailer".DS."includes".DS."loader.php");
		//global $AZMAILER;
		//if(!$AZMAILER){$AZMAILER = new \AZMailer\AZMailerCore();}
	}


	public function onAfterInitialise() {
		//TODO: now we have config option for this: this should be moved inside component when setting it to true
		// or on control panel when enabled but JMail is not modded
		//$this->checkJoomlaMailer();
	}

	public function onBeforeRender() {
	}

	/** param name: enable_joomla_jmail_override
	 * TODO: this is not good here - move it in component and let user activate/disactivate this
	 * TODO: This must be reversed when uninstalling plugin/component
	 *
	 * This method will:
	 * 1) load our own mailer class
	 * 2) check and replace class "extend" on Joomla's JMail class
	 *
	 * NOTES:
	 * For now we do this here because after a Joomla update JMail will be overwritten
	 * so we need to make sure it's always corrected
	 */
	private function checkJoomlaMailer() {
		if(!JFactory::getApplication()->isAdmin()){return;}
		$JMailPath = JPATH_LIBRARIES.DS."joomla".DS."mail";
		$content = file_get_contents($JMailPath.DS."mail.php");
		if(!preg_match('/\/\/AZMailer/', $content)) {
			//create a backup copy of the unmodified mail.php
			copy($JMailPath.DS."mail.php", $JMailPath.DS."mail.php.bck");

			//modify content
			$content = preg_replace('/class JMail extends PHPMailer/',
				'//AZMailer Mod: renaming JMail class to JMailOriginal'
				. "\n"
				. 'class JMailOriginal extends PHPMailer',
				$content
			);

			$content .= "\n\n"
				. '//AZMailer Mod: adding new class definitions'
				. "\n" . 'if (file_exists(JPATH_PLUGINS.DS."system".DS."azmailer".DS."classes".DS."AZMailerJMailExtension.php")) {'
				. "\n\t" . 'require_once(JPATH_PLUGINS.DS."system".DS."azmailer".DS."classes".DS."AZMailerJMailExtension.php");'
				. "\n\t" . 'class JMail extends AZMailerJMailExtension{}'
				. "\n" . '}';

			file_put_contents($JMailPath.DS."mail.php", $content);
		}
	}

}
