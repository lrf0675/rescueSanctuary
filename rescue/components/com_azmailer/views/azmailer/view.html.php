<?php
/**
 * @package    AZMailer
 * @subpackage Views
 * @author     Adam Jakab {@link http://www.alfazeta.com}
 * @author     Created on 09-Feb-2013
 * @license    GNU/GPL
 */

//-- No direct access
defined('_JEXEC') or die('Restricted access');

//-- Import the JView class
jimport('joomla.application.component.view');
use AZMailer\Helpers\AZMailerNewsletterHelper;

/**
 * HTML View class for the AZMailer Component.
 * Class AZMailerViewAZMailer
 */
class AZMailerViewAZMailer extends JViewLegacy {
	/** @var bool */
	protected $confirmed = false;

	/** @var string */
	protected $removalError;

	/** @var string */
	protected $CTRL;

	/**
	 * @throws Exception
	 */
	public function removeMeFromNewsletter() {
		$JI = \JFactory::getApplication()->input;
		$this->removalError = null;
		$this->CTRL = $JI->getString("ctrl", "");
		$this->confirmed = $JI->getInt("confirmed", 0);
		if (!empty($this->CTRL)) {
			if ( ($validMailToRemove = AZMailerNewsletterHelper::checkAndGetValidMailToRemoveFromNewsletter($this->CTRL)) ) {
				if ($this->confirmed == 1) {
					if (\JSession::checkToken()) {
						$chkemail = $JI->getString("chkemail", "");
						if (!empty($chkemail) && $chkemail == $validMailToRemove) {
							if (AZMailerNewsletterHelper::blacklistNewsletterSubscriber($validMailToRemove)) {
								//we are ok
							} else {
								$this->removalError = \JText::_("COM_AZMAILER_SUBSCR_REMOVAL_ERR_5");//"Unknown error! No e-mail was removed.";
							}
						} else {
							$this->removalError = \JText::_("COM_AZMAILER_SUBSCR_REMOVAL_ERR_4");//"The inserted e-mail does not correspond to that of the newsletter! Please make sure to insert the same e-mail address through which you have received the newsletter. No e-mail was removed.";
						}
					} else {
						$this->removalError = \JText::_("COM_AZMAILER_SUBSCR_REMOVAL_ERR_3");//"Forged token! No e-mail was removed.";
					}
				}
			} else {
				$this->removalError = \JText::_("COM_AZMAILER_SUBSCR_REMOVAL_ERR_2");//"The supplied control string is not valid! No e-mail was removed.";
			}
		} else {
			$this->removalError = \JText::_("COM_AZMAILER_SUBSCR_REMOVAL_ERR_1");//"No control string was found! No e-mail was removed.";
		}
		parent::display("removemefromnewsletter");
	}
}
