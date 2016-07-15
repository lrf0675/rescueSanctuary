<?php
/**
 * Main file to be called from crontab with something like:
 * *\/5 * * * * /usr/bin/php [WEB-SITE-ROOT]/administrator/components/com_azmailer/cli/azmailer.php
 */

// Bootstrap the application(setup paths, libraries and class loader)
require_once("code/bootstrap.php");

// Set all loggers to echo.
\JLog::addLogger(array('logger' => 'echo'), JLog::ALL);

// Set up and run the application.
try {
	$application = JApplicationCli::getInstance('AZMailer\Cli\Application\AZMailerApplicationCli');
	JFactory::$application = $application;
	$application->execute();
} catch (Exception $e) {
	fwrite(STDOUT, $e->getMessage() . "\n");
	exit($e->getCode());
}
