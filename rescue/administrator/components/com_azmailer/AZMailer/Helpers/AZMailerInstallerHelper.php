<?php
namespace AZMailer\Helpers;
/**
 * @package    AZMailer
 * @subpackage Helpers
 * @author     Adam Jakab {@link http://www.alfazeta.com}
 * @author     Created on 09-Feb-2013
 * @license    GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
use AZMailer\AZMailerCore;

/**
 * Installer Helper Class
 * This class will be used by the install/script.php installer script
 *
 * @author jackisback
 */
class AZMailerInstallerHelper {
	/** @var  \AZMailer\AZMailerCore */
	private static $AZMailerCore;
	private static $messages = array();

	/**
	 * Executed on: install, update, discover_install
	 * Install Folder: [JPATH_ROOT]/tmp/install_0123456789abcdef
	 * Component Folder: NONE(when first time install!!!)
	 *
	 * @param string            $type - can be any of install, update, discover_install
	 * @param string            $installFolder
	 * @param string            $componentFolder
	 * @param \JAdapterInstance $parent - $parent->getParent will return an instance of the \JInstaller class
	 * @return boolean - returning false will halt the execution
	 */
	public static function preflight($type, $installFolder, $componentFolder, $parent) {
		$answer = true;
		self::addMessage(($answer ? "success" : "error"), "Pre-checks");
		return ($answer);
	}

	/**
	 * @param string $type
	 * @param string $message
	 */
	private static function addMessage($type, $message) {
		if (in_array($type, array("info", "success", "warning", "error")) && !empty($message)) {
			array_push(self::$messages, array("type" => $type, "message" => $message));
		}
	}

	/**
	 * Executed on: install
	 * Install Folder: [JPATH_ROOT]/tmp/install_0123456789abcdef
	 * Component Folder: [JPATH_ROOT]/administrator/components/[com_component]
	 *
	 * @param string            $installFolder
	 * @param string            $componentFolder
	 * @param \JAdapterInstance $parent - $parent->getParent will return an instance of the \JInstaller class
	 * @return bool - returning false will halt the execution
	 */
	public static function install($installFolder, $componentFolder, $parent) {
		$answer = true;
		self::addMessage(($answer ? "success" : "error"), "Component Install");
		return ($answer);
	}

	/**
	 * Executed on: update
	 * Install Folder: [JPATH_ROOT]/tmp/install_0123456789abcdef
	 * Component Folder: [JPATH_ROOT]/administrator/components/[com_component]
	 *
	 * @param string            $installFolder
	 * @param string            $componentFolder
	 * @param \JAdapterInstance $parent - $parent->getParent will return an instance of the \JInstaller class
	 * @return bool - returning false will halt the execution
	 */
	public static function update($installFolder, $componentFolder, $parent) {
		$answer = true;
		self::addMessage(($answer ? "success" : "error"), "Component Update");
		return ($answer);
	}

	/**
	 * Executed on: install, update, discover_install
	 * Install Folder: [JPATH_ROOT]/tmp/install_0123456789abcdef
	 * Component Folder: [JPATH_ROOT]/administrator/components/[com_component]
	 * Note: You cannot halt execution by returning false like the other methods here
	 *
	 * @param string            $type - can be any of install, update, discover_install
	 * @param string            $installFolder
	 * @param string            $componentFolder
	 * @param \JAdapterInstance $parent - $parent->getParent will return an instance of the \JInstaller class
	 */
	public static function postflight($type, $installFolder, $componentFolder, $parent) {
		$answer = self::recheckConfiguration();
		self::addMessage(($answer ? "success" : "error"), "Post-check(configuration checks)");
		$answer = self::executeDatabaseUpdater();
		self::addMessage(($answer ? "success" : "error"), "Post-check(database updater)");
		$answer = self::enablePlugins();
		self::addMessage(($answer ? "success" : "error"), "Post-check(plugin enabler)");
		$answer = self::setupFilesAndFolders();
		self::addMessage(($answer ? "success" : "error"), "Post-check(files/folders)");
		$answer = self::cleanupAndCheckUpdateSites();
		self::addMessage(($answer ? "success" : "warning"), "Post-check(update sites)");
		//
		self::dumpMessages();
	}

	//---------------------------------------------------------------------------------------------------PRIVATE METHODS

	/**
	 * (POSTFLIGHT) - Rechecks configuration options and adds default configuration values where missing
	 * @return bool
	 */
	private static function recheckConfiguration() {
		$answer = self::setupAzmailerCore();
		if ($answer) {
			AZMailerComponentParamHelper::recheckConfigurationAndSetDefaultConfiguration();
		}
		return ($answer);
	}

	/**
	 * @return bool
	 */
	private static function setupAzmailerCore() {
		$answer = false;
		if (!self::$AZMailerCore) {
			if (class_exists('AZMailer\AZMailerCore')) {
				self::$AZMailerCore = new AZMailerCore();
				$answer = true;
			} else {
				self::addMessage("error", "AZMailerCore class does not exists!");
			}
		} else {
			$answer = true;
		}
		return ($answer);
	}

	/**
	 * (POSTFLIGHT) - Updates database
	 * @return bool
	 */
	private static function executeDatabaseUpdater() {
		$answer = self::setupAzmailerCore();
		if ($answer) {
			$AZMDBUH = new AZMailerDBUpdaterHelper();
			$answer = $AZMDBUH->update(false);//true is for verbose
		}
		return ($answer);
	}

	/**
	 * (POSTFLIGHT) - Enables plugins
	 * When component is installed from package at this point all plugins should be installed - IS THIS TRUE???
	 * but not enabled - so let's enable them
	 * @return bool
	 */
	private static function enablePlugins() {
		$db = \JFactory::getDBO();
		//azmailer system plugin
		$sql = 'UPDATE #__extensions SET enabled = 1 WHERE'
			. ' type = ' . $db->quote('plugin')
			. ' AND element = ' . $db->quote('azmailer')
			. ' AND folder = ' . $db->quote('system');
		$db->setQuery($sql);
		$db->execute();
		//azmailer user sync plugin
		$sql = 'UPDATE #__extensions SET enabled = 1 WHERE'
			. ' type = ' . $db->quote('plugin')
			. ' AND element = ' . $db->quote('azmailerusersync')
			. ' AND folder = ' . $db->quote('user');
		$db->setQuery($sql);
		$db->execute();
		return (true);
	}

	/**
	 * (POSTFLIGHT) - Puts .htaccess file in attachments folder denying direct access to files
	 * @return bool
	 */
	private static function setupFilesAndFolders() {
		$answer = self::setupAzmailerCore();
		if ($answer) {
			//WRITE .htaccess FILE FOR ATTACHMENTS FOLDER
			$attachmentsFolder = self::$AZMailerCore->getOption("newsletter_attachment_base");
			$htaccessContent = "# No direct access to this folder #\ndeny from all\n";
			file_put_contents(JPATH_ROOT . DS . $attachmentsFolder . DS . '.htaccess', $htaccessContent);
		}
		return ($answer);
	}

	/**
	 * Due to quite a few changes in the update sites, urls, xml problems, etc - various deployments may have left behind
	 * various update sites which are not any more available(all on domain dev.alfazeta.com).
	 * In particular an error in v.1.4.7 has left behind and empty url update site with name "AZMailer Update Server"
	 * The only correct update site is:
	 *  name="JAB Update Server" location="http://devshed.jakabadambalazs.com/updates.xml"
	 * So, this method will
	 * 1) remove all update sites with domain: "dev.alfazeta.com*" or name "AZMailer*"
	 *
	 * @return bool
	 */
	private static function cleanupAndCheckUpdateSites() {
		$db = \JFactory::getDBO();

		//get update site id list
		$q = $db->getQuery(true);
		$q->select("res.update_site_id")
			->from("#__update_sites AS res")
			->where("(res.name LIKE " . $db->quote("%AZMailer%") . " OR res.location LIKE " . $db->quote("http://dev.alfazeta.com%") . ")");
		$db->setQuery($q);
		$idlist = $db->loadColumn();
		if ($idlist && is_array($idlist) && count($idlist)) {
			//remove bad update sites
			$sql = "DELETE FROM #__update_sites WHERE update_site_id IN (" . implode($idlist, ",") . ");";
			$db->setQuery($sql);
			$db->execute();
			//remove connected update site extensions
			$sql = "DELETE FROM #__update_sites_extensions WHERE update_site_id IN (" . implode($idlist, ",") . ");";
			$db->setQuery($sql);
			$db->execute();
		}
		return (true);
	}

	private static function dumpMessages() {
		$html = '';
		if (count(self::$messages)) {
			$html .= '<table class="table table-bordered">';
			foreach (self::$messages as $ma) {
				$color = 'transparent';
				if ($ma["type"] == "success") {
					$color = "#589d56";
				}
				if ($ma["type"] == "warning") {
					$color = "#F2B876";
				}
				if ($ma["type"] == "error") {
					$color = "#FF2B40";
				}
				$html .= '<tr>';
				$html .= '<td style="min-width:150px; border-bottom:1px solid #bababa;">' . $ma["message"] . '</td>';
				$html .= '<td style="background-color:' . $color . '; border-bottom:1px solid #bababa; text-align:center;">' . $ma["type"] . '</td>';
				$html .= '</tr>';
			}
			$html .= '</table>';
		}
		echo $html;
	}

	/**
	 * Executed on: uninstall
	 * Install Folder: [JPATH_ROOT]/administrator/components/[com_component]
	 * Component Folder: [JPATH_ROOT]/administrator/components/[com_component]
	 * Note: You cannot halt execution by returning false like the other methods here
	 *
	 * @param string            $installFolder
	 * @param string            $componentFolder
	 * @param \JAdapterInstance $parent - $parent->getParent will return an instance of the \JInstaller class
	 */
	public static function uninstall($installFolder, $componentFolder, $parent) {
		$answer = self::removeDatabaseTables();
		self::addMessage(($answer ? "success" : "error"), "Component Uninstall");
		//
		self::dumpMessages();
	}

	/**
	 * This is executed on uninstall
	 */
	private static function removeDatabaseTables() {
		$answer = self::setupAzmailerCore();
		if ($answer) {
			if (self::$AZMailerCore->getOption("remove_dbtables_on_uninstall", true)) {
				$AZMDBUH = new AZMailerDBUpdaterHelper();
				$answer = $AZMDBUH->removeAllTables(false);
				self::addMessage("info", "Your AZMailer database tables were removed.");
			} else {
				self::addMessage("info", "Your AZMailer database tables were not removed.");
			}
		}
		return ($answer);
	}
}

