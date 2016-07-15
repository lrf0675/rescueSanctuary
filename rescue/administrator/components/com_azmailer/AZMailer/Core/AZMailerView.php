<?php
namespace AZMailer\Core;
/**
 * @package    AZMailer
 * @subpackage Core
 * @author     Adam Jakab {@link http://www.alfazeta.com}
 * @author     Created on 09-Feb-2013
 * @license    GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.view');

/**
 * Class AZMailerView
 * @package AZMailer\Core
 */
class AZMailerView extends \JViewLegacy {
	/** @var mixed $item */
	protected $item;

	/** @var mixed $items */
	protected $items;

	/** @var mixed $state */
	protected $state;

	/** @var mixed $filters */
	protected $filters;

	/** @var mixed $pagination */
	protected $pagination;

	/**
	 * @param array $config
	 */
	function __construct($config = array()) {
		parent::__construct($config);
	}

	/**
	 * Default view display method
	 * @param null $tpl
	 * @return mixed|void
	 */
	function display($tpl = null) {
		return (parent::display($tpl));
	}
}
