<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
use AZMailer\Helpers\AZMailerAdminInterfaceHelper;
use AZMailer\Helpers\AZMailerLocationHelper;
use AZMailer\Helpers\AZMailerSubscriberHelper;

JHtml::_('behavior.tooltip');
global $AZMAILER;
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));

/** @var \JRegistry|\Joomla\Registry\Registry - AZMailer Settings params */
$params = $this->state->get('params');

$SHOW_WHAT = $this->state->get('filter.location_type');
$currentTreeObjectName = ($SHOW_WHAT == "country" ? JText::_('COM_AZMAILER_LOCATION_COUNTRY') : ($SHOW_WHAT == "region" ? JText::_('COM_AZMAILER_LOCATION_REGION') : JText::_('COM_AZMAILER_LOCATION_PROVINCE')));
$currentTreeObjectParentName = ($SHOW_WHAT == "country" ? "&nbsp;" : ($SHOW_WHAT == "region" ? JText::_('COM_AZMAILER_LOCATION_COUNTRY') : JText::_('COM_AZMAILER_LOCATION_COUNTRY') . " - " . JText::_('COM_AZMAILER_LOCATION_REGION')));


//BUTTONS TO SELECT "COUNTRY" / "REGION" / "PROVINCE"
$SHOW_WHAT_BUTTONS = '<ul style="list-style:none;margin:0;padding:0;">';
//COUNTRY
$disabled = ("country" == $this->state->get('filter.location_type') ? ' disabled="disabled" ' : ' ');
$onclick = ("country" == $this->state->get('filter.location_type') ? ' ' : ' onclick="document.getElementById(\'filter_location_type\').value=\'country\';this.form.submit();" ');
$SWName = JText::_('COM_AZMAILER_LOCATION_COUNTRIES');
$SHOW_WHAT_BUTTONS .= '<li style="float:left; margin: 0 5px 0 0;">'
	. '<button class="btn btn-info"' . $disabled . 'value="' . $SWName . '" title="' . $SWName . '"' . $onclick . '>'.$SWName.'</button>'
	. '</li>';
//REGION
$disabled = ("region" == $this->state->get('filter.location_type') ? ' disabled="disabled" ' : ' ');
$onclick = ("region" == $this->state->get('filter.location_type') ? ' ' : ' onclick="document.getElementById(\'filter_location_type\').value=\'region\';this.form.submit();" ');
$SWName = JText::_('COM_AZMAILER_LOCATION_REGIONS');
$SHOW_WHAT_BUTTONS .= '<li style="float:left; margin: 0 5px 0 0;">'
	. '<button class="btn btn-info"' . $disabled . 'value="' . $SWName . '" title="' . $SWName . '"' . $onclick . '>'.$SWName.'</button>'
	. '</li>';
//PROVINCE
$disabled = ("province" == $this->state->get('filter.location_type') ? ' disabled="disabled" ' : ' ');
$onclick = ("province" == $this->state->get('filter.location_type') ? ' ' : ' onclick="document.getElementById(\'filter_location_type\').value=\'province\';this.form.submit();" ');
$SWName = JText::_('COM_AZMAILER_LOCATION_PROVINCES');
$SHOW_WHAT_BUTTONS .= '<li style="float:left; margin: 0 5px 0 0;">'
	. '<button class="btn btn-info"' . $disabled . 'value="' . $SWName . '" title="' . $SWName . '"' . $onclick . '>'.$SWName.'</button>'
	. '</li>';

$SHOW_WHAT_BUTTONS .= '</ul>';




?>
<?php echo AZMailerAdminInterfaceHelper::displaySubmenu(); ?>
<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm" id="adminForm">
	<fieldset id="filter-bar">
		<div class="filter-search fltlft">
			<?php echo JText::_('COM_AZMAILER_LOCATION_DESC_SEL_LOC'); ?>
			<?php echo $SHOW_WHAT_BUTTONS; ?>
		</div>
		<div class="filter-select fltrt">
			<table>
				<tr>
					<th align="left"><?php echo(isset($this->filters['country']) ? JText::_('COM_AZMAILER_LOCATION_COUNTRY_IN') : "&nbsp;"); ?></th>
					<th align="left"><?php echo(isset($this->filters['region']) ? JText::_('COM_AZMAILER_LOCATION_REGION_IN') : "&nbsp;"); ?></th>
				</tr>
				<tr>
					<td><?php echo(isset($this->filters['country']) ? $this->filters['country'] : "&nbsp;"); ?></td>
					<td><?php echo(isset($this->filters['region']) ? $this->filters['region'] : "&nbsp;"); ?></td>
				</tr>
			</table>
		</div>
	</fieldset>


	<table class="adminlist table table-striped table-bordered table-hover">
		<thead>
		<th width="50"><?php echo JHtml::_('grid.sort', 'COM_AZMAILER_LOCATION_SIGLA_SHORT', 'itemSigla', $listDirn, $listOrder); ?></th>
		<th><?php echo JHtml::_('grid.sort', $currentTreeObjectName, 'itemName', $listDirn, $listOrder); ?></th>
		<th><?php echo $currentTreeObjectParentName; ?></th>
		<th width="50"><?php echo JText::_('COM_AZMAILER_LOCATION_TIT_COUNT'); ?></th>
		<th width="50"><?php echo JText::_('COM_AZMAILER_OPERATIONS'); ?></th>
		</thead>
		<tfoot>
		<tr>
			<td colspan="100"><?php echo $this->pagination->getListFooter(); ?></td>
		</tr>
		</tfoot>
		<tbody>
		<?php
		foreach ($this->items as $i => $item):
			$ABBR = $item->itemSigla;
			$ITEMNAME = '<a href="javascript:void(0);" onclick="changeElementName(' . $item->id . ',\'' . base64_encode($item->itemName) . '\', \'' . base64_encode($item->itemSigla) . '\')">' . $item->itemName . '</a>';
			$PARENTNAME = $item->parentName;

			$COUNT = 0;
			if ($SHOW_WHAT == "country") {
				$COUNT_1 = AZMailerLocationHelper::countRegionsInCountry($item->id);
				$COUNT_2 = AZMailerSubscriberHelper::countSubscribersInCountry($item->id);
			} else if ($SHOW_WHAT == "region") {
				$COUNT_1 = AZMailerLocationHelper::countProvincesInRegion($item->id);
				$COUNT_2 = AZMailerSubscriberHelper::countSubscribersInRegion($item->id);
			} else if ($SHOW_WHAT == "province") {
				$COUNT_1 = 0;
				$COUNT_2 = AZMailerSubscriberHelper::countSubscribersInProvince($item->id);
			}
			$COUNT = $COUNT_1 + $COUNT_2;

			$DELETE = '&nbsp;';
			if ($COUNT == 0) {
				$DELETE = '<a onclick="deleteElement(' . $item->id . ',\'' . base64_encode($item->itemName) . '\')" style="float:right; cursor:pointer;" title="' . JText::_('COM_AZMAILER_DELETE') . '"><span class="ui-icon ui-icon-circle-minus"></span></a>';
			}
			?>

			<tr class="row<?php echo $i % 1; ?>">
				<td align="center"><?php echo $ABBR; ?></td>
				<td><strong><?php echo $ITEMNAME; ?></strong></td>
				<td><?php echo $PARENTNAME; ?></td>
				<td align="right"><?php echo $COUNT_1 . "/" . $COUNT_2; ?></td>
				<td align="right"><?php echo $DELETE; ?></td>
			</tr>

		<?php
		endforeach;
		?>
		</tbody>
	</table>
	<input type="hidden" name="option" value="<?php echo $AZMAILER->getOption("com_name"); ?>"/>
	<input type="hidden" name="task" value="<?php echo $AZMAILER->getOption("ctrl.task"); ?>"/>
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
	<input type="hidden" name="boxchecked" value="0"/>
	<input type="hidden" id="filter_location_type" name="filter_location_type"
	       value="<?php echo $this->state->get('filter.location_type'); ?>"/>
	<?php echo JHtml::_('form.token'); ?>
</form>

<div id="mDialog" title="dialog">
	<h3 class="title"></h3>

	<p class="text"></p>
</div>

<script language="javascript" type="text/javascript">
	Joomla.submitbutton = function (pressbutton) {
		if (pressbutton == 'location.new') {
			addNewElement();
			return (false);
		}
		submitform(pressbutton);
	};

	jQuery(document).ready(function ($) {
		$("#mDialog").dialog({
			width: 600,
			autoOpen: false,
			modal: true,
			closeOnEscape: false,
			draggable: false,
			resizable: false
		});

	});

	function addNewElement() {
		if ("<?php echo $SHOW_WHAT; ?>" == "region") {
			if (jQuery('select#filter_country option:selected').val() == 0) {
				alert("<?php echo JText::_('COM_AZMAILER_LOCATION_ERR_NO_COUNTRY'); ?>");
				return (false);
			}
		}
		if ("<?php echo $SHOW_WHAT; ?>" == "province") {
			if (jQuery('select#filter_country option:selected').val() == 0 || jQuery('select#filter_region option:selected').val() == 0) {
				alert("<?php echo JText::_('COM_AZMAILER_LOCATION_ERR_NO_COUNTRY_NO_REGION'); ?>");
				return (false);
			}
		}

		jQuery('#mDialog').dialog("option", "title", "<?php echo JText::_('COM_AZMAILER_ADD'); ?> <?php echo $currentTreeObjectName; ?>");
		jQuery('#mDialog .title').html("");
		var formhtml = '';
		formhtml += '<?php echo JText::_('COM_AZMAILER_NAME'); ?>:<br /><input name="nto_name" size="70" maxlength="64" value="" /><br /><br />';
		formhtml += '<?php echo JText::_('COM_AZMAILER_LOCATION_SIGLA'); ?>:<br /><input name="nto_sigla" size="70" maxlength="8" value="" /><br />';
		jQuery('#mDialog .text').html(formhtml);

		jQuery('#mDialog').dialog("option", "buttons", [
				{
					text: "<?php echo JText::_('COM_AZMAILER_ADD'); ?>",
					click: function () {
						var name = jQuery("#mDialog .text input[name=nto_name]").val();
						var sigla = jQuery("#mDialog .text input[name=nto_sigla]").val();
						if (name.length == 0) {
							alert("<?php echo JText::_('COM_AZMAILER_LOCATION_ERR_EMPTY_NAME'); ?>");
							return (false);
						}
						jQuery.post("index.php", {
								option: jQuery("form#adminForm input[name=option]").val(),
								task: "location.addNew",
								format: "raw",
								add_what: "<?php echo $SHOW_WHAT; ?>",
								name: name,
								sigla: sigla,
								country_id: jQuery('select#filter_country option:selected').val(),
								region_id: jQuery('select#filter_region option:selected').val()
							},
							function (data) {
								elaborateJsonResponse(data, true, "location.display");
							}
						);
					}
				},
				{
					text: "<?php echo JText::_('COM_AZMAILER_CANCEL'); ?>",
					click: function () {
						jQuery(this).dialog("close");
					}
				}
			]
		);
		jQuery('#mDialog').dialog('open');
	}

	function changeElementName(objId, name, sigla) {
		jQuery('#mDialog').dialog("option", "title", "<?php echo JText::_('COM_AZMAILER_MODIFY'); ?> <?php echo $currentTreeObjectName; ?>");
		jQuery('#mDialog .title').html("");
		var formhtml = '';
		formhtml += '<?php echo JText::_('COM_AZMAILER_NAME'); ?>:<br /><input name="nto_name" size="70" maxlength="64" value="' + jQuery.base64Decode(name) + '" /><br /><br />';
		formhtml += '<?php echo JText::_('COM_AZMAILER_LOCATION_SIGLA'); ?>:<br /><input name="nto_sigla" size="70" maxlength="8" value="' + jQuery.base64Decode(sigla) + '" /><br />';
		jQuery('#mDialog .text').html(formhtml);
		jQuery('#mDialog').dialog("option", "buttons", [
				{
					text: "<?php echo JText::_('COM_AZMAILER_MODIFY'); ?>",
					click: function () {
						var name = jQuery("#mDialog .text input[name=nto_name]").val();
						var sigla = jQuery("#mDialog .text input[name=nto_sigla]").val();
						if (name.length == 0) {
							alert("<?php echo JText::_('COM_AZMAILER_LOCATION_ERR_EMPTY_NAME'); ?>");
							return (false);
						}
						jQuery.post("index.php", {
								option: jQuery("form#adminForm input[name=option]").val(),
								task: "location.changeName",
								format: "raw",
								change_what: "<?php echo $SHOW_WHAT; ?>",
								name: name,
								sigla: sigla,
								id: objId
							},
							function (data) {
								elaborateJsonResponse(data, true, "location.display");
							}
						);

					}
				},
				{
					text: "<?php echo JText::_('COM_AZMAILER_CANCEL'); ?>",
					click: function () {
						jQuery(this).dialog("close");
					}
				}
			]
		);
		jQuery('#mDialog').dialog('open');
	}

	function deleteElement(objId) {
		if (confirm("<?php echo JText::_('COM_AZMAILER_LOCATION_DEL_CONFIRM'); ?> <?php echo $currentTreeObjectName; ?>?")) {
			jQuery.post("index.php", {
					option: jQuery("form#adminForm input[name=option]").val(),
					task: "location.delete",
					format: "raw",
					delete_what: "<?php echo $SHOW_WHAT; ?>",
					id: objId
				},
				function (data) {
					elaborateJsonResponse(data, true, "location.display");
				}
			);
		}
	}


	function elaborateJsonResponse(data, showErrors, reloadTask) {
		var answer = (typeof data != "object" ? JSON.parse(data) : data);
		if (answer.result && reloadTask != "") {
			submitform(reloadTask);
		} else {
			if (showErrors && answer.errors.length > 0) {
				alert(answer.errors);
			}
		}
	}

</script>