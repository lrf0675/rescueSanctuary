<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
use AZMailer\Helpers\AZMailerAdminInterfaceHelper;

JHtml::_('behavior.tooltip');

global $AZMAILER;
$item = &$this->item;
?>
<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm" id="adminForm">
	<fieldset class="adminform">
		<legend><?php /*echo $TITLE;*/ ?></legend>
		<table class="admintable" style="width:100%;">
			<?php
			echo AZMailerAdminInterfaceHelper::getInputFileldRow(JText::_('COM_AZMAILER_TEMPLATE_CODE'), "tpl_code", $item->tpl_code, 30, 64, false, ($item->tpl_code == "default"));
			echo AZMailerAdminInterfaceHelper::getInputFileldRow(JText::_('COM_AZMAILER_NAME'), "tpl_name", $item->tpl_name, 80, 255);
			echo AZMailerAdminInterfaceHelper::getInputFileldRow(JText::_('COM_AZMAILER_TEMPLATE_DEFAULT_TITLE'), "tpl_title", $item->tpl_title, 80, 255);
			?>
		</table>
	</fieldset>

	<input type="hidden" name="option" value="<?php echo $AZMAILER->getOption("com_name"); ?>"/>
	<input type="hidden" name="task" value="<?php echo $AZMAILER->getOption("ctrl.task"); ?>"/>
	<input type="hidden" name="id" value="<?php echo $item->id; ?>"/>
	<input type="hidden" name="tpl_type" value="newsletter"/>
	<?php echo JHtml::_('form.token'); ?>
</form>

<script language="javascript" type="text/javascript">
	Joomla.submitbutton = function (pressbutton) {

		if (pressbutton == 'template.display') {
			Joomla.submitform(pressbutton);
			return;
		}
		var form = jQuery("form#adminForm");
		// do field validation
		if (document.getElementById("tpl_code").value == "") {
			alert("<?php echo JText::_('COM_AZMAILER_TEMPLATE_ERR_CODE'); ?>");
			return false;
		}
		if (document.getElementById("tpl_name").value == "") {
			alert("<?php echo JText::_('COM_AZMAILER_TEMPLATE_ERR_NAME'); ?>");
			return false;
		}
		if (document.getElementById("tpl_title").value == "") {
			alert("<?php echo JText::_('COM_AZMAILER_TEMPLATE_ERR_TITLE'); ?>");
			return false;
		}

		Joomla.submitform(pressbutton);
	};

	//-->
</script>