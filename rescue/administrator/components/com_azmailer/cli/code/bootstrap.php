<?php
/**
 * Bootstrap
 */
// Set the Joomla execution flag so cli won't die
define('_JEXEC', 1);

/**
 * !---Development option---!
 * Since we are developing AZMailer in a symlinked environment where the component codebase
 * is shared between both J!2.5 and J!3
 * the realpath(__DIR__) would return the actual(not the symlinked) path resulting in not finding Joomla stuff
 * So, as a dev option when calling this (azmailer.php) file we reserve an option as path for JPATH_BASE
 * in this manner: php azmailer.php --someOption --anotherOption --JPATH_BASE="/path/to/joomla/root"
 * This is absolutely NOT necessary for normal installations!
 */
if (count($argv)) {//;) it must be otherwise you wouldn't be here
	foreach ($argv as $arg) {
		if (substr($arg, 0, strlen("--JPATH_BASE=")) == "--JPATH_BASE=") {
			define('JPATH_BASE', substr($arg, strlen("--JPATH_BASE=")));
		}
	}
}

// Define the path for the Joomla Platform
defined('JPATH_BASE') || define('JPATH_BASE', realpath(__DIR__ . '/../../../../..'));
define('JPATH_COMPONENT_SITE', JPATH_BASE . '/components/com_azmailer');
define('JPATH_COMPONENT_ADMINISTRATOR', JPATH_BASE . '/administrator/components/com_azmailer');
define('JPATH_CLIBASE', realpath(__DIR__ . '/..'));


//check if JPAH_BASE is correct by checking for J! configuration file in site root
if (!file_exists(JPATH_BASE . "/configuration.php")) {
	echo("Cannot find Joomla root directory! JPATH_BASE is wrong!\n");
	echo("Please use the '--JPATH_BASE=joomla/root/directory' argument to adjust.\n");
	die();
}

// Import the platform(s).
require_once JPATH_BASE . '/libraries/import.php';
require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/libraries/cms.php';
require_once JPATH_CONFIGURATION . '/configuration.php';

//Import Azmailer defines
require_once JPATH_COMPONENT_ADMINISTRATOR . '/includes/defines.php';
$jVersion = new \JVersion();


//Load JApp classes for J!3
if (IS_J3) {
	JLoader::import('joomla.input.input');//(JInput)
	JLoader::import('joomla.table.table');//(JTable)
	JLoader::import('cms.installer.installer');//(JInstaller)
	if (version_compare($jVersion->RELEASE, '3.1.6', '<=')) {
		JLoader::import('legacy.component.helper');//(JComponentHelper)
	} else {
		JLoader::import('cms.component.helper');//(JComponentHelper)
	}
	JLoader::import('joomla.uri.uri');//(JUri)
} else {
	JLoader::import('joomla.application.input');//(JInput)
	JLoader::import('joomla.database.table');//(JTable)
	JLoader::import('joomla.installer.installer');//(JInstaller)
	JLoader::import('joomla.application.component.helper');//(JComponentHelper)
	JLoader::import('joomla.environment.uri');//(JUri)
}

//Import Azmailer autoloader
require_once JPATH_COMPONENT_ADMINISTRATOR . '/includes/loader.php';

//Create the AZMailer core class - NO INIT!
$AZMC = new \AZMailer\AZMailerCore();

// Setup the specific autoloader for CLI
spl_autoload_register(function ($class) {
	if (preg_match('#^AZMailer\\\Cli\\\#', $class)) {
		$classpath = str_replace('AZMailer\\Cli\\', '', $class);
		$classpath = str_replace('\\', '/', $classpath) . '.php';
		$realpath = realpath(JPATH_CLIBASE . DS . 'code' . DS . $classpath);
		if ($realpath) {
			//echo "\nAutoloading Class($class): $classpath - $realpath";
			require_once($realpath);
		} else {
			echo "\nAutoloading Class Not Found($class): $classpath !";
		}
	} else {
		//echo "\nNOT AZMailer CLI Class($class)!";
	}

}, true, true);


