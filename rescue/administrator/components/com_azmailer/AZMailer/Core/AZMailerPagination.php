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
jimport('joomla.html.pagination');

/**
 * Class AZMailerPagination
 * @package AZMailer\Core
 */
class AZMailerPagination extends \JPagination {


	/**
	 * Creates a dropdown box for selecting how many records to show per page. - REMOVING ALL option
	 *
	 * @return  string  The HTML for the limit # input box.
	 *
	 * @since   11.1
	 */
	public function getLimitBox() {
		$app = \JFactory::getApplication();

		// Initialise variables.
		$limits = array();

		// Make the option list.
		for ($i = 10; $i <= 30; $i += 10) {
			$limits[] = \JHtml::_('select.option', "$i");
		}
		$limits[] = \JHtml::_('select.option', '50');
		$limits[] = \JHtml::_('select.option', '100');
		$limits[] = \JHtml::_('select.option', '250');
		//$limits[] = \JHtml::_('select.option', '0', "ALL RECORDS");

		//J25 has _viewall and J3 has viewall
		$viewAll = (isset($this->_viewall) ? $this->_viewall : $this->viewall);
		$selected = $viewAll ? 0 : $this->limit;

		// Build the select list.
		if ($app->isAdmin()) {
			$html = \JHtml::_(
				'select.genericlist',
				$limits,
				$this->prefix . 'limit',
				'class="inputbox" size="1" onchange="Joomla.submitform();"',
				'value',
				'text',
				$selected
			);
		} else {
			$html = \JHtml::_(
				'select.genericlist',
				$limits,
				$this->prefix . 'limit',
				'class="inputbox" size="1" onchange="this.form.submit()"',
				'value',
				'text',
				$selected
			);
		}
		return $html;
	}


}