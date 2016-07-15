<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
use AZMailer\Entities\AZMailerSubscriber;
use AZMailer\Helpers\AZMailerAdminInterfaceHelper;
use AZMailer\Helpers\AZMailerCategoryHelper;

JHtml::_('behavior.tooltip');

/** $AZMAILER AZMailer\AZMailerCore */
global $AZMAILER;
/** @var $item AZMailerSubscriber */
$item = &$this->item;


$TITLE = ($item->get("id") ? "Editing subscriber:" . $item->get("nls_email") : "Creating new subscriber");

?>
<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm" id="adminForm">
	<fieldset class="adminform">
		<legend><?php echo(isset($TITLE) ? $TITLE : ''); ?></legend>
		<table class="adminlist edittable" style="width:100%;">
			<?php
			echo AZMailerAdminInterfaceHelper::getInputFileldRow(JText::_('COM_AZMAILER_SUBSCR_TIT_EMAIL'), "nls_email", $item->get("nls_email"), 60, 128);
			echo AZMailerAdminInterfaceHelper::getInputFileldRow(JText::_('COM_AZMAILER_SUBSCR_FIRSTNAME'), "nls_firstname", $item->get("nls_firstname"), 60, 64);
			echo AZMailerAdminInterfaceHelper::getInputFileldRow(JText::_('COM_AZMAILER_SUBSCR_LASTNAME'), "nls_lastname", $item->get("nls_lastname"), 60, 64);

			//YN - BLACKLIST
			$lst = AZMailerAdminInterfaceHelper::getSelectOptions_YesNo();
			$lstdef = ($item->getIsBlacklisted() ? "Y" : "N");
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
				$lstdef = ($item->get("nls_cat_" . $CN) != "" ? json_decode($item->get("nls_cat_" . $CN)) : AZMailerCategoryHelper::getDefaultOptionsArrayForCategory($CN));
				$SB_CAT[$CN] = JHTML::_('select.genericlist', $lst, 'nls_cat_' . $CN . '[]', 'class="inputbox" multiple="multiple" size="15" style="min-width:180px;"', 'id', 'data', $lstdef);
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
	<input type="hidden" name="id" value="<?php echo $item->get("id"); ?>"/>
	<?php echo JHtml::_('form.token'); ?>
</form>

<script language="javascript" type="text/javascript">

	jQuery(document).ready(function ($) {
		var def_selection_country = parseInt("<?php echo $item->get("nls_country_id"); ?>");
		var def_selection_region = parseInt("<?php echo $item->get("nls_region_id"); ?>");
		var def_selection_province = parseInt("<?php echo $item->get("nls_province_id"); ?>");


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
		}
		checkFormData(pressbutton);
		//Joomla.submitform( pressbutton );
	};

	function checkFormData(pressbutton) {
		jQuery.post("index.php", {
				option: jQuery("form#adminForm input[name=option]").val(),
				task: "subscriber.check_nls_data",
				format: "raw",
				id: jQuery("form[name=adminForm] input[name=id]").val(),
				nls_email: jQuery('form[name=adminForm] input[name=nls_email]').val(),
				nls_firstname: jQuery('form[name=adminForm] input[name=nls_firstname]').val(),
				nls_lastname: jQuery('form[name=adminForm] input[name=nls_lastname]').val()
			},
			function (data) {
				try {
					var parsedData = (typeof data != "object" ? JSON.parse(data) : data);
				} catch (e) {
					parsedData.errors[0] = "Unable to parse string!\n" + e;
				}
				var errorcount = parsedData.errors.length;
				resetErrorCheckSpans();
				if (errorcount > 0) {
					var fname;
					var fmsg;
					for (var i = 0; i < errorcount; i++) {
						fname = parsedData.errors[i].field;
						fmsg = parsedData.errors[i].message;
						jQuery('form#adminForm span#err_' + fname).html(fmsg);
					}
				} else {
					Joomla.submitform(pressbutton);
				}
			}
		);
	}

	function resetErrorCheckSpans() {
		jQuery('form#adminForm span.jqerror').attr('class', 'jqerror').html('');
	}

	//-->
</script>