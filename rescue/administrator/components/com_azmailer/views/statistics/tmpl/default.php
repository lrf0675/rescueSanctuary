<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
JHtml::_('behavior.tooltip');
global $AZMAILER;
use AZMailer\Helpers\AZMailerAdminInterfaceHelper;

?>
<?php echo AZMailerAdminInterfaceHelper::displaySubmenu(); ?>
<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm" id="adminForm">
	<table class="adminlist">
		<tr>
			<td>
				<h2>COMING SOON!</h2>
			</td>
		</tr>
	</table>
	<input type="hidden" name="option" value="<?php echo $AZMAILER->getOption("com_name"); ?>"/>
	<input type="hidden" name="task" value="<?php echo $AZMAILER->getOption("ctrl.task"); ?>"/>
	<input type="hidden" name="boxchecked" value="0"/>
	<?php echo JHtml::_('form.token'); ?>
</form>
