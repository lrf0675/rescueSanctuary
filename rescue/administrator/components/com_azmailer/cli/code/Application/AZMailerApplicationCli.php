<?php

namespace AZMailer\Cli\Application;

use AZMailer\Cli\Model\AZMailerCron;
use Joomla\Registry\Registry;

defined('_JEXEC') or die('Restricted access');
/**
 * @package     AZMailer
 * @subpackage  Cli\Application
 */

// Allow the application to run as long as is necessary.
ini_set('max_execution_time', 0);

/**
 * Class AZMailerApplicationCli
 * @package AZMailer\Cli\Application
 */
class AZMailerApplicationCli extends \JApplicationCli {
	/** @var Registry */
	protected $config;

	/**
	 * @param \JInputCli   $input
	 * @param Registry $config
	 * @param \JEventDispatcher $dispatcher
	 */
	public function __construct(\JInputCli $input = null, Registry $config = null, \JEventDispatcher $dispatcher = null) {
		parent::__construct($input, $config, $dispatcher);
		//now we have configuration read in:
		//$this->out("Verbose:" . $this->config->get('verbose'));
		if ($this->config->get('verbose')) {
			error_reporting(E_ALL);
			ini_set('display_errors', true);
		}


	}

	/**
	 * Execute the application.
	 */
	public function doExecute() {
		$I = new \JInputCli();
		if ($I->get('help')) {//cmd line param: --help
			$this->_help();
		} else {
			//default app execution
			$AZMC = new AZMailerCron($this->config);
			$AZMC->executeTasks();
		}
	}

	private function _help() {
		$this->out('AZMailer CLI');
		$this->out('Please refer to the users guide for usage.');
		$this->out('For normal operation use: php /path/to/joomla/administrator/components/com_azmailer/cli/azmailer.php');
	}

	/**
	 * @param string $text
	 * @param bool $nl - add line break at end of line?
	 * @return void
	 */
	public function out($text = '', $nl = true) {
		if ($this->config->get('verbose')) {
			parent::out($text, $nl);
		}
	}

	/**
	 * Fetch the configuration data for the application.
	 *
	 * @return  object  An object to be loaded into the application configuration.
	 *
	 * @since   1.0
	 * @throws  \RuntimeException if file cannot be read.
	 */
	protected function fetchConfigurationData() {
		$configPath = JPATH_CLIBASE . '/config/';
		$configFile = (file_exists($configPath . '/config.json') ? $configPath . '/config.json' : $configPath . '/config.dist.json');
		if (!is_readable($configFile)) {
			throw new \RuntimeException('Configuration file does not exist or is unreadable.');
		}
		$config = json_decode(file_get_contents($configFile));
		return $config;
	}
}
