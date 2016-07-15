<?php
// No direct access to this file
defined('_JEXEC') or die();
global $AZMAILER;
?>
<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm" id="adminForm">
	<input type="hidden" name="option" value="<?php echo $AZMAILER->getOption("com_name"); ?>"/>
	<input type="hidden" name="task" value="<?php echo $AZMAILER->getOption("ctrl.task"); ?>"/>
</form>
