<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
use AZMailer\Helpers\AZMailerAdminInterfaceHelper;
use AZMailer\Helpers\AZMailerCategoryHelper;

JHtml::_('behavior.tooltip');

/** $AZMAILER AZMailer\AZMailerCore */
global $AZMAILER;
?>
<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm" id="adminForm"
      enctype="multipart/form-data">
	<fieldset class="adminform">
		<h3><?php echo \JText::_('COM_AZMAILER_SUBSCR_TIT_IMPORTXLS'); ?></h3>
		<table class="adminlist edittable" style="width:100%;">
			<tr>
				<td colspan="2">
					<?php echo \JText::_("COM_AZMAILER_SUBSCR_DESC_IMPORTXLS"); ?>
				</td>
			</tr>

			<tr>
				<td class="data_name"><?php echo JText::_("COM_AZMAILER_SUBSCR_TIT_SELECT_FILE"); ?></td>
				<td class="data_val">
					<input class="inputbox" type="file" name="nls_contacts_file" id="nls_contacts_file" size="50"
					       value=""/>
				</td>
			</tr>

			<tr>
				<td class="data_name" valign="top"><?php echo JText::_("COM_AZMAILER_SUBSCR_IMPORT_OVERWRITE"); ?></td>
				<td>
					<table width="100%" border="0">
						<tr>
							<td style="height:auto; padding:0;">
								<input class="inputbox" type="radio" name="nls_overwrite_existing" value="1"
								       style="margin:0 3px;"/>
								<?php echo JText::_("COM_AZMAILER_SUBSCR_IMPORT_OVERWRITE_OP1"); ?>
							</td>
						</tr>
						<tr>
							<td style="height:auto; padding:0;">
								<input class="inputbox" type="radio" name="nls_overwrite_existing" value="2"
								       style="margin:0 3px;"/>
								<?php echo JText::_("COM_AZMAILER_SUBSCR_IMPORT_OVERWRITE_OP2"); ?>
							</td>
						</tr>
						<tr>
							<td style="height:auto; padding:0;">
								<input class="inputbox" type="radio" name="nls_overwrite_existing" value="3"
								       checked="checked" style="margin:0 3px;"/>
								<?php echo JText::_("COM_AZMAILER_SUBSCR_IMPORT_OVERWRITE_OP3"); ?>
							</td>
						</tr>
					</table>
				</td>
			</tr>

			<tr>
				<td></td>
				<td><b><?php echo JText::_("COM_AZMAILER_SUBSCR_IMPORT_TIT_PREDEFINED"); ?></b></td>
			</tr>

			<?php


			//YN - BLACKLIST
			$lst = AZMailerAdminInterfaceHelper::getSelectOptions_YesNo();
			$lstdef = "N";
			$SB_BLACKLISTED = \JHTML::_('select.genericlist', $lst, 'nls_blacklisted', 'class="inputbox" size="1"', 'id', 'data', $lstdef);
			echo AZMailerAdminInterfaceHelper::getInputFileldRow(JText::_('COM_AZMAILER_SUBSCR_IN_BLACKLIST'), "nls_blacklisted", null, null, null, null, null, 'custom', $SB_BLACKLISTED);

			//empty selectBoxes for country/region/province - will load by jQuery
			$SB_COUNTRY = JHTML::_('select.genericlist', array(), 'nls_country_id', 'class="inputbox" size="1" style="min-width:200px;"', 'id', 'data', 0);
			$SB_REGION = JHTML::_('select.genericlist', array(), 'nls_region_id', 'class="inputbox" size="1" style="min-width:200px;"', 'id', 'data', 0);
			$SB_PROVINCE = JHTML::_('select.genericlist', array(), 'nls_province_id', 'class="inputbox" size="1" style="min-width:200px;"', 'id', 'data', 0);

			//CATEGORY SELECT BOXES
			$SB_CAT = array();
			for ($CN = 1; $CN <= 5; $CN++) {
				$lst = AZMailerCategoryHelper::getSelectOptions_CatItems($CN, false);
				$lstdef = AZMailerCategoryHelper::getDefaultOptionsArrayForCategory($CN);
				$SB_CAT[$CN] = JHTML::_('select.genericlist', $lst, 'nls_cat_' . $CN . '[]', 'class="inputbox" multiple="multiple" size="15" style="width:100%;"', 'id', 'data', $lstdef);
			}


			?>

			<tr bgcolor="#efefef">
				<td class="data_name" style="vertical-align: top;">
					<label><?php echo JText::_("COM_AZMAILER_SUBSCR_GEOPOS"); ?></label></td>
				<td class="data_val">
					<table width="100%" border="0">
						<tr>
							<th><?php echo JText::_("COM_AZMAILER_LOCATION_COUNTRY"); ?></th>
							<th><?php echo JText::_("COM_AZMAILER_LOCATION_REGION"); ?></th>
							<th><?php echo JText::_("COM_AZMAILER_LOCATION_PROVINCE"); ?></th>
						</tr>
						<tr>
							<td><?php echo $SB_COUNTRY; ?></td>
							<td><?php echo $SB_REGION; ?></td>
							<td><?php echo $SB_PROVINCE; ?></td>
						</tr>
					</table>
				</td>
			</tr>

			<tr bgcolor="#efefef">
				<td class="data_name" style="vertical-align: top;">
					<?php echo JText::_("COM_AZMAILER_SUBSCR_IN_CATEGORY"); ?>
					<br/><br/><em><?php echo JText::_("COM_AZMAILER_SUBSCR_IN_CATEGORY_HELP"); ?></em>
				</td>
				<td class="data_val">
					<table width="100%" border="0">
						<tr>
							<th><?php echo $AZMAILER->getOption("category_name_1"); ?></th>
							<th><?php echo $AZMAILER->getOption("category_name_2"); ?></th>
							<th><?php echo $AZMAILER->getOption("category_name_3"); ?></th>
							<th><?php echo $AZMAILER->getOption("category_name_4"); ?></th>
							<th><?php echo $AZMAILER->getOption("category_name_5"); ?></th>
						</tr>
						<tr>
							<td><?php echo $SB_CAT[1]; ?></td>
							<td><?php echo $SB_CAT[2]; ?></td>
							<td><?php echo $SB_CAT[3]; ?></td>
							<td><?php echo $SB_CAT[4]; ?></td>
							<td><?php echo $SB_CAT[5]; ?></td>
						</tr>
					</table>
				</td>
			</tr>


		</table>
	</fieldset>

	<input type="hidden" name="option" value="<?php echo $AZMAILER->getOption("com_name"); ?>"/>
	<input type="hidden" name="task" value="<?php echo $AZMAILER->getOption("ctrl.task"); ?>"/>
</form>

<div id="mDialog" title="dialog">
	<p class="content"></p>

	<p class="errors" style="color:#800000;"></p>
</div>

<script language="javascript" type="text/javascript">

	jQuery(document).ready(function ($) {
		var def_selection_country = 0;
		var def_selection_region = 0;
		var def_selection_province = 0;

		$("#mDialog").dialog({
			width: 600,
			autoOpen: false,
			modal: true,
			closeOnEscape: false,
			draggable: false,
			resizable: false
		});


		$("select#nls_country_id").change(function () {
			updateRegions();
		});
		$("select#nls_region_id").change(function () {
			updateProvinces();
		});

		function updateCountries() {
			$("select#nls_country_id").children().remove();
			$.post("index.php", {
					option: jQuery("form#adminForm input[name=option]").val(),
					task: "subscriber.getSelectOptionsCountries",
					format: "raw"
				},
				function (data) {
					var parsedData = elaborateJsonResponse(data, true);
					$.each(parsedData, function (k, d) {
						$("select#nls_country_id").append($('<option>', {value: d.id}).text(d.data));
						if (d.id == def_selection_country) {
							$("select#nls_country_id option[value=" + d.id + "]").attr("selected", "selected");
						}
					});
					updateRegions();
				}
			);
		}


		function updateRegions() {
			$("select#nls_region_id").children().remove();
			$.post("index.php", {
					option: jQuery("form#adminForm input[name=option]").val(),
					task: "subscriber.getSelectOptionsRegions",
					format: "raw",
					country_id: $("select#nls_country_id").val()
				},
				function (data) {
					var parsedData = elaborateJsonResponse(data, true);
					$.each(parsedData, function (k, d) {
						$("select#nls_region_id").append($('<option>', {value: d.id}).text(d.data));
						if (d.id == def_selection_region) {
							$("select#nls_region_id option[value=" + d.id + "]").attr("selected", "selected");
						}
					});
					updateProvinces();
				}
			);
		}


		function updateProvinces() {
			$("select#nls_province_id").children().remove();
			$.post("index.php", {
					option: jQuery("form#adminForm input[name=option]").val(),
					task: "subscriber.getSelectOptionsProvinces",
					format: "raw",
					region_id: $("select#nls_region_id").val()
				},
				function (data) {
					var parsedData = elaborateJsonResponse(data, true);
					$.each(parsedData, function (k, d) {
						$("select#nls_province_id").append($('<option>', {value: d.id}).text(d.data));
						if (d.id == def_selection_province) {
							$("select#nls_province_id option[value=" + d.id + "]").attr("selected", "selected");
						}
					});
				}
			);
		}

		function elaborateJsonResponse(data, showErrors) {
			var errors = false;
			var answer = false;
			try {
				var jsonObj = (typeof data != "object" ? JSON.parse(data) : data);
				errors = jsonObj.errors;
				answer = jsonObj.result;
			} catch (e) {
				errors = "Unable to parse string!\n" + e;
			}
			if (showErrors && errors.length > 0) {
				alert("ERROR!\n" + errors);
			}
			return (answer);
		}

		//auto update on document load
		updateCountries();

	});


	Joomla.submitbutton = function (pressbutton) {
		if (pressbutton == 'subscriber.display') {
			Joomla.submitform(pressbutton);
			return;
		} else if (pressbutton == 'subscriber.importSubscribers') {
			checkFormData(pressbutton);
		}

		return false;
	};

	//file upload
	var mDialog = jQuery("#mDialog");

	function checkFormData(pressbutton) {
		mDialog.dialog("option", "title", "Subscriber Importer");
		var dialogContent = ''
			+ 'Uploading file: <span class="percent">0%</span>'
			+ '';
		jQuery('.content', mDialog).html(dialogContent);
		jQuery('.errors', mDialog).html('');
		mDialog.dialog("option", "buttons", []);
		mDialog.dialog('open');
		//
		jQuery('form#adminForm').ajaxSubmit({
			data: {
				option: "<?php echo $AZMAILER->getOption("com_name"); ?>",
				task: "subscriber.importSubscribers",
				format: "raw"
			},
			dataType: 'json',
			success: uploadProcessSuccess,
			error: processError,
			uploadProgress: function (ev, pos, tot, perc) {
				jQuery('.content span.percent', mDialog).html(perc + "%");
			}
		});
	}

	function uploadProcessSuccess(resp, status, xhr, $form) {
		if (resp.errors.length > 0) {
			processError(resp, status, resp.errors);
			return false;
		}
		var resHtml = '' +
			'Lines in file: ' + resp["result"]["lines in file"] + '<br />' +
			'Raw lines: ' + resp["result"]["elaborated raw lines"] + '<br />' +
			'Valid imports: ' + resp["result"]["valid imports"] + '<br />' +
			'Registered subscribers: ' + resp["result"]["imported subscribers"] + '<br />' +
			'';
		jQuery('.content', mDialog).html(resHtml);
		mDialog.dialog("option", "buttons", [
				{
					text: "<?php echo JText::_('COM_AZMAILER_CLOSE'); ?>",
					click: function () {
						mDialog.dialog("close");
						Joomla.submitbutton("subscriber.display");
					}
				}
			]
		);

	}

	function processError(response, status, err) {
		var errorMessages = '';
		if (Object.prototype.toString.call(err) === '[object Array]') {
			for (var i = 0; i < err.length; i++) {
				errorMessages += err[i] + '<br />';
			}
		} else {
			errorMessages = err;
		}
		jQuery('.errors', mDialog).html(errorMessages);
		mDialog.dialog("option", "buttons", [
				{
					text: "<?php echo JText::_('COM_AZMAILER_CANCEL'); ?>",
					click: function () {
						mDialog.dialog("close");
					}
				}
			]
		);
	}


	//-->
</script>