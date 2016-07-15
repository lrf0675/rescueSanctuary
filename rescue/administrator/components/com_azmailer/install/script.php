<?php
/**
 * @package    AZMailer
 * @author     Adam Jakab {@link http://dev.alfazeta.com}
 * @author     Created on 31-May-2013
 * @license    GNU/GPL
 */

//-- No direct access
defined('_JEXEC') || die('Access denied!');


/**
 * Script file for AZMailer component.
 */
class com_azmailerInstallerScript {
	/** @var string */
	private $componentName = 'com_azmailer';
	/** @var  string */
	private $installFolder;
	/** @var  string */
	private $componentFolderAdmin;

	/**
	 * Executed on: install, update, discover_install
	 * Install Folder: [JPATH_ROOT]/tmp/install_0123456789abcdef
	 * Component Folder: NONE(when first time install!!!)
	 *
	 * @param string            $type - can be any of install, update, discover_install
	 * @param \JAdapterInstance $parent - $parent->getParent will return an instance of the \JInstaller class
	 * @return boolean - returning false will halt the execution
	 */
	public function preflight($type, $parent) {
		$res = $this->setupPaths($type);
		if ($res) {
			$res = AZMailer\Helpers\AZMailerInstallerHelper::preflight($type, $this->installFolder, $this->componentFolderAdmin, $parent);
		}
		return ($res);
	}

	/**
	 * We need to require defines.php and loader.php for component from the includes folder
	 * There are two possibilities:
	 * 1) during installation/update it is in the uncompressed package folder: ./admin/includes
	 * 2) during uninstall it will be under the installed components folder: JPATH_ROOT."/administrator/components/com_azmailer/includes
	 * @param string $type - one of: install, update, discover_install, uninstall
	 * @return bool - returns true/false if AZMailerInstallerHelper was found
	 */
	private function setupPaths($type = null) {
		if (!in_array($type, array("install", "update", "discover_install", "uninstall"))) {
			return false;
		}
		$this->installFolder = realpath(dirname(__DIR__));
		$this->componentFolderAdmin = JPATH_ROOT . "/administrator/components/" . $this->componentName;
		if (!file_exists($this->componentFolderAdmin)) {
			$this->componentFolderAdmin = false;
		}
		$includesFolder = $this->installFolder . ($type != "uninstall" ? "/admin" : "") . "/includes";
		$answer = $this->setupAZMailerInstallerHelper($includesFolder);
		return ($answer);
	}

	/**
	 * Load defines.php and loader.php from the includes folder
	 * @param string $includesFolder
	 * @return bool
	 */
	private function setupAZMailerInstallerHelper($includesFolder) {
		//echo "<br />setupAZMailerInstallerHelper: FOLDER: " . $includesFolder;
		$answer = false;
		if (!class_exists('AZMailer\Helpers\AZMailerInstallerHelper')) {
			if (file_exists($includesFolder . "/defines.php")) {
				require_once($includesFolder . "/defines.php");
				if (file_exists($includesFolder . "/loader.php")) {
					require_once($includesFolder . "/loader.php");
					if (class_exists('AZMailer\Helpers\AZMailerInstallerHelper')) {
						$answer = true;
					} else {
						echo "<br />AZMailerInstallerHelper: class does NOT exist!";
					}
				} else {
					echo "<br />AZMailerInstallerHelper: loader.php does NOT exist($includesFolder)!";
				}
			} else {
				echo "<br />AZMailerInstallerHelper: defines.php does NOT exist($includesFolder)!";
			}
		} else {
			$answer = true;
		}
		return ($answer);
	}

	/**
	 * Executed on: install
	 * Install Folder: [JPATH_ROOT]/tmp/install_0123456789abcdef
	 * Component Folder: [JPATH_ROOT]/administrator/components/[com_component]
	 *
	 * @param \JAdapterInstance $parent - $parent->getParent will return an instance of the \JInstaller class
	 * @return bool - returning false will halt the execution
	 */
	public function install($parent) {
		$res = $this->setupPaths("install");
		if ($res) {
			$res = AZMailer\Helpers\AZMailerInstallerHelper::install($this->installFolder, $this->componentFolderAdmin, $parent);
		}
		return ($res);
	}

	/**
	 * Executed on: update
	 * Install Folder: [JPATH_ROOT]/tmp/install_0123456789abcdef
	 * Component Folder: [JPATH_ROOT]/administrator/components/[com_component]
	 *
	 * @param \JAdapterInstance $parent - $parent->getParent will return an instance of the \JInstaller class
	 * @return bool - returning false will halt the execution
	 */
	public function update($parent) {
		$res = $this->setupPaths("update");
		if ($res) {
			$res = AZMailer\Helpers\AZMailerInstallerHelper::update($this->installFolder, $this->componentFolderAdmin, $parent);
		}
		return ($res);
	}

	/**
	 * Executed on: install, update, discover_install
	 * Install Folder: [JPATH_ROOT]/tmp/install_0123456789abcdef
	 * Component Folder: [JPATH_ROOT]/administrator/components/[com_component]
	 * Note: You cannot halt execution by returning false like the other methods here
	 *
	 * @param string            $type - can be any of install, update, discover_install
	 * @param \JAdapterInstance $parent - $parent->getParent will return an instance of the \JInstaller class
	 */
	public function postflight($type, $parent) {
		$res = $this->setupPaths($type);
		if ($res) {
			AZMailer\Helpers\AZMailerInstallerHelper::postflight($type, $this->installFolder, $this->componentFolderAdmin, $parent);
		}
	}

	/**
	 * Executed on: uninstall
	 * Install Folder: [JPATH_ROOT]/administrator/components/[com_component]
	 * Component Folder: [JPATH_ROOT]/administrator/components/[com_component]
	 * Note: You cannot halt execution by returning false like the other methods here
	 *
	 * @param \JAdapterInstance $parent - $parent->getParent will return an instance of the \JInstaller class
	 */
	public function uninstall($parent) {
		$res = $this->setupPaths("uninstall");
		if ($res) {
			AZMailer\Helpers\AZMailerInstallerHelper::uninstall($this->installFolder, $this->componentFolderAdmin, $parent);
		}
	}
}
