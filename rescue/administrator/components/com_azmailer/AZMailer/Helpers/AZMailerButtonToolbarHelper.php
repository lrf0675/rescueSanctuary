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


/**
 * Class AZMailerButtonToolbarHelper
 * @package AZMailer\Helpers
 */
class AZMailerButtonToolbarHelper {
	/**
	 * Stores the singleton instances of various toolbar.
	 *
	 * @var    array
	 * @since  2.5
	 */
	protected static $instances = array();
	/**
	 * Toolbar name
	 *
	 * @var    string
	 */
	protected $_name = array();
	/**
	 * Toolbar array
	 *
	 * @var    array
	 */
	protected $_bar = array();
	/**
	 * Loaded buttons
	 *
	 * @var    array
	 */
	protected $_buttons = array();
	/**
	 * Directories, where button types can be stored.
	 *
	 * @var    array
	 */
	protected $_buttonPath = array();

	/**
	 * @param string $name
	 */
	public function __construct($name = 'toolbar') {
		$this->_name = $name;
	}

	/**
	 * Returns the global AZMailerButtonToolbarHelper object, only creating it if it
	 * doesn't already exist.
	 *
	 * @param   string $name The name of the toolbar.     *
	 * @return  AZMailerButtonToolbarHelper  The AZMailerButtonToolbarHelper object.
	 */
	public static function getInstance($name = 'toolbar') {
		if (empty(self::$instances[$name])) {
			self::$instances[$name] = new AZMailerButtonToolbarHelper($name);
		}
		return self::$instances[$name];
	}

	/**
	 * Push button onto the end of the toolbar array.
	 * arguments: text, link, active
	 * @return bool
	 */
	public function appendButton() {
		$btn = func_get_args();
		array_push($this->_bar, $btn);
		return true;
	}

	/**
	 * Insert button into the front of the toolbar array.
	 * aruments: same as appendButton
	 * @return  string
	 */
	public function prependButton() {
		$btn = func_get_args();
		array_unshift($this->_bar, $btn);
		return true;
	}

	/**
	 * Get the list of toolbar links
	 * @return  array
	 */
	public function getItems() {
		return $this->_bar;
	}

	/**
	 * Get the name of the toolbar.
	 * @return  string
	 */
	public function getName() {
		return $this->_name;
	}

	/**
	 * Render
	 * @return  string  HTML for the toolbar.
	 */
	public function render() {
		$html = array();
		$html[] = '<div class="toolbarMenu">';
		$html[] = '<ul class="submenu">';
		foreach ($this->_bar as $button) {
			$html[] = $this->renderButton($button);
		}
		$html[] = '</ul>';
		$html[] = '</div>';
		return implode('', $html);
	}

	/**
	 * Render a button.
	 * @param   object &$button array with args: text, link, active
	 * @return  string
	 */
	private function renderButton(&$button) {
		$html = array();
		$html[] = '<li>';
		$html[] = '<a ' . ($button[2] ? 'class="active"' : '') . 'href="' . $button[1] . '">';
		$html[] = $button[0];
		$html[] = '</a>';
		$html[] = '</i>';

		return implode('', $html);
	}
}
