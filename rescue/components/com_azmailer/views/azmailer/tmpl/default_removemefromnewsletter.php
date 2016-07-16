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
global $AZMAILER;
?>
<?php if (!$this->removalError) : ?>
	<?php if (!$this->confirmed) : ?>
		<h1 class="title"><?php echo \JText::_("COM_AZMAILER_SUBSCR_REMOVAL"); ?></h1>
		<p><?php echo \JText::_("COM_AZMAILER_SUBSCR_REMOVAL_DESC"); ?></p>
		<form action="index.php" method="post" name="form_remove_me_from_newsletter">
			<input type="hidden" name="option" value="<?php echo $AZMAILER->getOption("com_name"); ?>" />
			<input type="hidden" name="task" value="azmailer.removeMeFromNewsletter" />
			<input type="hidden" name="ctrl" value="<?php echo $this->CTRL; ?>" />
			<input type="hidden" name="confirmed" value="1" />
			<input type="text" name="chkemail" placeholder="E-mail" size="60" />
			<input name="submit" type="submit" value="<?php echo \JText::_("COM_AZMAILER_SUBSCR_REMOVAL_SUBMIT"); ?>"/>
			<?php echo \JHTML::_( 'form.token' ); ?>
		</form>
	<?php else: ?>
		<h1 class="title"><?php echo \JText::_("COM_AZMAILER_SUBSCR_REMOVAL"); ?></h1>
		<p><?php echo \JText::_("COM_AZMAILER_SUBSCR_REMOVAL_DONE_DESC"); ?></p>
	<?php endif; ?>
<?php else: ?>
	<h1 class="title"><?php echo \JText::_("COM_AZMAILER_SUBSCR_REMOVAL"); ?></h1>
	<p class="error"><?php echo $this->removalError; ?></p>
<?php endif; ?>