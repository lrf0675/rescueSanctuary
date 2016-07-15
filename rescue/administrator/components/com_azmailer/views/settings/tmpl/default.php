<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
JHtml::_('behavior.tooltip');
global $AZMAILER;
use AZMailer\Helpers\AZMailerAdminInterfaceHelper;
use AZMailer\Helpers\AZMailerComponentParamHelper;

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
$cols = 0;
?>
<?php echo AZMailerAdminInterfaceHelper::displaySubmenu(); ?>
<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm" id="adminForm">
	<table class="adminlist table table-striped table-bordered table-hover">
		<thead>
		<tr>
			<th width="250"><?php echo JText::_('COM_AZMAILER_SETTING_PCODE');
				$cols++; ?></th>
			<th width="250"><?php echo JText::_('COM_AZMAILER_SETTING_PNAME');
				$cols++; ?></th>
			<th><?php echo JText::_('COM_AZMAILER_SETTING_PVALUE');
				$cols++; ?></th>
		</tr>
		</thead>
		<tbody>
		<?php
		$paramGroups = AZMailerComponentParamHelper::getParamGroups();
		foreach ($paramGroups as $paramGroup):
			?>
			<tr class="header">
				<td colspan="<?php echo $cols; ?>"><?php echo \JText::_("COM_AZMAILER_SETTING_PCAT_" . strtoupper($paramGroup)); ?></td>
			</tr>
			<?php
			$groupParams = AZMailerComponentParamHelper::getParamsInGroup($paramGroup);
			$i = 0;
			foreach ($groupParams as $paramKey => $groupParam):

				$PKLINK = '<a href="javascript:void(0);" onclick="changeParam(\'' . $paramKey . '\')" title="' . $groupParam["description"] . '">' . $groupParam["label"] . '</a>';

				$paramValue = $AZMAILER->getOption($paramKey, true);
				switch ($groupParam["type"]) {
					case "list":
						$paramValue = AZMailerComponentParamHelper::getParamValueNameFromList($paramKey, $paramValue);
						break;
				}
				?>
				<tr class="row<?php echo $i++ % 2; ?>">
					<td><?php echo $paramKey; ?></td>
					<td><?php echo $PKLINK; ?><br/>
						<small><?php echo $groupParam["description"]; ?></small>
					</td>
					<td class="pv pv_<?php echo $paramKey; ?>"><?php echo $paramValue; ?></td>
				</tr>
			<?php
			endforeach;//group params
		endforeach;//param groups
		?>
		</tbody>
	</table>

	<input type="hidden" name="option" value="<?php echo $AZMAILER->getOption("com_name"); ?>"/>
	<input type="hidden" name="task" value="<?php echo $AZMAILER->getOption("ctrl.task"); ?>"/>
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
	<input type="hidden" name="boxchecked" value="0"/>
	<?php echo JHtml::_('form.token'); ?>
</form>

<div id="mDialog" class="mDialog settings" title="dialog">
	<div class="content"></div>
	<div class="msg"></div>
</div>


<script language="javascript" type="text/javascript">
	jQuery(document).ready(function ($) {
		$("#mDialog").dialog({
			width: 600,
			autoOpen: false,
			modal: true,
			closeOnEscape: false,
			draggable: true,
			resizable: false
		});
	});

	function elaborateJsonResponse(data, showErrors) {
		var answer = (typeof data != "object" ? JSON.parse(data) : data);
		if (showErrors && answer.errors.length > 0) {
			alert(answer.errors);
		}
		return (answer);
	}

	function changeParam(paramName) {
		var mDialog = jQuery('#mDialog');
		mDialog.dialog("option", "title", "<?php echo JText::_('COM_AZMAILER_SETTING_MOD_PARAM'); ?>");
		var content = '...';
		jQuery('.content', mDialog).html(content);
		jQuery('.msg', mDialog).html("");
		mDialog.dialog('open');

		jQuery.post("index.php", {
				option: jQuery("form#adminForm input[name=option]").val(),
				task: "settings.getParamEditForm",
				format: "raw",
				paramName: paramName
			},
			function (data) {
				var answer = elaborateJsonResponse(data, true);
				if (answer.errors.length > 0) {
					mDialog.dialog('close');
					return;
				}
				jQuery('.content', mDialog).html(answer.result);
				mDialog.dialog("option", "buttons", [
						{
							text: "<?php echo JText::_('COM_AZMAILER_SET'); ?>",
							click: function () {
								jQuery('.msg', mDialog).html("");
								var newValue = jQuery("input[name=paramValue], textarea[name=paramValue], select[name=paramValue]", mDialog).val();
								jQuery.post("index.php", {
										option: jQuery("form#adminForm input[name=option]").val(),
										task: "settings.submitParamEditForm",
										format: "raw",
										paramName: paramName,
										paramValue: newValue
									},
									function (data) {
										var answer = elaborateJsonResponse(data, false);
										if (answer.errors.length > 0) {
											jQuery('.msg', mDialog).html(JSON.stringify(answer.errors));
										} else {
											if (jQuery("input[name=paramValue], textarea[name=paramValue], select[name=paramValue]", mDialog).prop("tagName") == "SELECT") {
												newValue = jQuery("select[name=paramValue] option[value=" + newValue + "]", mDialog).html();
											}
											jQuery(".adminlist td.pv_" + paramName).html(newValue).effect("highlight", {color: "#b83e0f"}, 1500);
											mDialog.dialog('close');
										}
									}
								);
							}
						},
						{
							text: "<?php echo JText::_('COM_AZMAILER_CANCEL'); ?>",
							click: function () {
								mDialog.dialog('close');
							}
						}
					]
				);
			}
		);
	}

	Joomla.submitbutton = function (pressbutton) {
		if (pressbutton == 'settings.checkAndUpdateAZMailerTables') {
			if (!confirm("<?php echo JText::_( 'COM_AZMAILER_SETTING_CHECKDB_CONFIRM' ); ?>")) {
				return (false);
			}
		}
		submitform(pressbutton);
	}
</script>