<?php
namespace AZMailer\Core;
/**
 * @author     Adam Jakab {@link http://www.alfazeta.com}
 * @author     Created on 21-Mar-2013
 * @license    GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');

/**
 * Class AZMailerException
 * @package AZMailer\Core
 */
class AZMailerException extends \Exception {
	/**
	 * @param string     $message
	 * @param int        $code
	 * @param \Exception $previous
	 */
	public function __construct($message = "", $code = 0, \Exception $previous = null) {
		parent::__construct($message, $code, $previous);
	}

	/**
	 * @return string
	 */
	public function getFormattedErrorMessage() {
		$answer = '';
		$answer .= '<h3>(' . $this->getCode() . ') ' . $this->getMessage() . '</h3>';
		return ($answer);
	}
}