<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
use AZMailer\Entities\AZMailerNewsletter;
use AZMailer\Helpers\AZMailerAdminInterfaceHelper;
use AZMailer\Helpers\AZMailerCategoryHelper;
use AZMailer\Helpers\AZMailerDateHelper;
use AZMailer\Helpers\AZMailerNewsletterHelper;

JHtml::_('behavior.tooltip');

/** $AZMAILER AZMailer\AZMailerCore */
global $AZMAILER;
/** @var $item AZMailerNewsletter */
$item = &$this->item;

//additional js
AZMailerAdminInterfaceHelper::addAdditionalHeaderIncludes('js', '/assets/js/newsletter/edit_content.js');
AZMailerAdminInterfaceHelper::addAdditionalHeaderIncludes('js', '/assets/js/newsletter/edit_recepients.js');
AZMailerAdminInterfaceHelper::addAdditionalHeaderIncludes('js', '/assets/js/newsletter/edit_attachments.js');


$SENDTESTMAIL = '<a name="nl_sendtestmail" style="float:right; margin-top:5px; cursor:pointer;" title="' . JText::_('COM_AZMAILER_NEWSLETTER_SEND_TEST') . '"><span class="ui-icon ui-icon-mail-closed"></span></a>';
$CHANGETEMPLATE = '<a name="nl_changetemplate" style="float:right; margin-top:5px; cursor:pointer;" title="' . JText::_('COM_AZMAILER_NEWSLETTER_TIT_MOD_TEMPLATE') . '"><span class="ui-icon ui-icon-image"></span></a>';

$JU = \JFactory::getUser();
$myMail = $JU->email;

?>

<div id="tabs">
	<ul>
		<li><a href="#tab-1"><?php echo JText::_('COM_AZMAILER_NEWSLETTER_TAB_1'); ?></a></li>
		<li><a href="#tab-2"><?php echo JText::_('COM_AZMAILER_NEWSLETTER_TAB_2'); ?></a></li>
		<li><a href="#tab-3"><?php echo JText::_('COM_AZMAILER_NEWSLETTER_TAB_3'); ?></a></li>
		<li><a href="#tab-4"><?php echo JText::_('COM_AZMAILER_NEWSLETTER_TAB_4'); ?></a></li>
	</ul>
	<div id="tab-1" style="padding:0;">
		<div class="ui-widget-content">
			<h2 class="ui-widget-header"><?php echo JText::_('COM_AZMAILER_NEWSLETTER_TAB_1'); ?><?php echo $SENDTESTMAIL; ?><?php echo $CHANGETEMPLATE; ?></h2>
			<?php
			$TITLE = $item->get("nl_title");
			$TITLE_INTERNAL = $item->get("nl_title_internal");
			$SENDER = $item->get("nl_email_from");
			$SENDER_NAME = $item->get("nl_email_from_name");
			$CREATION_DATE = AZMailerDateHelper::convertToHumanReadableFormat($item->get("nl_create_date"));
			$SEND_DATE = ($item->get("nl_send_date") != 0 ? AZMailerDateHelper::convertToHumanReadableFormat($item->get("nl_send_date"), true, true) : JText::_('COM_AZMAILER_NEWSLETTER_UNSENT'));
			//
			?>
			<table class="adminlist displaytable" name="nl_data">
				<tr>
					<td style="text-align:right; font-weight:bold;"><a
							title="<?php echo JText::_('COM_AZMAILER_NEWSLETTER_TIT_MOD_TITLE'); ?>" href="#"
							onClick="changeNewsletterTitle();"><?php echo JText::_('COM_AZMAILER_NEWSLETTER_TITLE'); ?>
							:</a></td>
					<td align="left" name="nl_title"><?php echo $TITLE; ?></td>
					<td style="text-align:right; font-weight:bold;"><a
							title="<?php echo JText::_('COM_AZMAILER_NEWSLETTER_TIT_MOD_TITLE'); ?>" href="#"
							onClick="changeNewsletterTitle();"><?php echo JText::_('COM_AZMAILER_NEWSLETTER_TITLE_INTERNAL'); ?>
							:</a></td>
					<td align="left" name="nl_title_internal"><?php echo $TITLE_INTERNAL; ?></td>
					<td style="text-align:right; font-weight:bold;"><?php echo JText::_('COM_AZMAILER_NEWSLETTER_CREATION_DATE'); ?>
						:
					</td>
					<td align="left"><?php echo $CREATION_DATE; ?></td>
				</tr>
				<tr>
					<td style="text-align:right; font-weight:bold;"><a
							title="<?php echo JText::_('COM_AZMAILER_NEWSLETTER_TIT_MOD_SENDER'); ?>" href="#"
							onClick="changeNewsletterSender();"><?php echo JText::_('COM_AZMAILER_NEWSLETTER_SENDER'); ?>
							:</a></td>
					<td align="left" name="nl_sender"><?php echo $SENDER; ?></td>
					<td style="text-align:right; font-weight:bold;"><a
							title="<?php echo JText::_('COM_AZMAILER_NEWSLETTER_TIT_MOD_SENDER'); ?>" href="#"
							onClick="changeNewsletterSender();"><?php echo JText::_('COM_AZMAILER_NEWSLETTER_SENDER_NAME'); ?>
							:</a></td>
					<td align="left" name="nl_sender_name"><?php echo $SENDER_NAME; ?></td>
					<td style="text-align:right; font-weight:bold;"><?php echo JText::_('COM_AZMAILER_NEWSLETTER_SEND_DATE'); ?>
						:
					</td>
					<td align="left"><?php echo $SEND_DATE; ?></td>
				</tr>
			</table>
			<table class="adminlist displaytable" name="nl_content">
				<tr>
					<td id="newslettercontent" style="padding:0">
						<iframe src="" frameborder="0" width="100%" id="iframednewslettercontent"></iframe>
					</td>
				</tr>
			</table>
		</div>
	</div>

	<?php
	$TXTCONTENTBUTTON = '<a name="nl_html2txt" style="float:right; margin-top:5px; cursor:pointer;" title="' . JText::_('COM_AZMAILER_NEWSLETTER_TIT_SIMPLETEXT') . '"><span class="ui-icon ui-icon-script"></span></a>';
	?>
	<div id="tab-2" style="padding:0;">
		<div class="ui-widget-content">
			<h2 class="ui-widget-header"><?php echo JText::_('COM_AZMAILER_NEWSLETTER_TAB_2'); ?><?php echo $TXTCONTENTBUTTON; ?></h2>
			<textarea name="nltxt"
			          style="width:100%; height:450px; "><?php echo $item->get("nl_textversion"); ?></textarea>
		</div>
	</div>


	<?php
	//--------------------ATTACHMENTS
	$ADDATTACHMENTBUTTON = '<a name="nl_addattachment" style="float:right; margin-top:5px; cursor:pointer;" title="' . JText::_('COM_AZMAILER_NEWSLETTER_TIT_ADD_ATTACHMENT') . '"><span class="ui-icon ui-icon-circle-plus"></span></a>';
	?>
	<div id="tab-3" style="padding:0;">
		<div class="ui-widget-content">
			<h2 class="ui-widget-header"><?php echo JText::_('COM_AZMAILER_NEWSLETTER_TAB_3'); ?><?php echo $ADDATTACHMENTBUTTON; ?></h2>

			<div id="NLATTACHMENTS"></div>
		</div>
	</div>


	<div id="tab-4" style="padding:0;">
		<h2 class="ui-widget-header">
			<?php echo JText::_('COM_AZMAILER_NEWSLETTER_TAB_4'); ?>
			<div style="float:right;"><?php echo JText::_('COM_AZMAILER_NEWSLETTER_NUM_RECIEVERS'); ?>:&nbsp;<span
					name="numberOfRecepients">0</span></div>
		</h2>
		<div class="ui-widget-content halfsize lefty">

			<h3 class="ui-widget-header"
			    style="height:25px;"><?php echo JText::_('COM_AZMAILER_NEWSLETTER_TIT_SEL_GEOPOS'); ?></h3>
			<fieldset name="locSelBehaviourDesc">
				<legend><?php echo JText::_('COM_AZMAILER_NEWSLETTER_DESC_SEL_GEOPOS'); ?></legend>
				<table width="100%" cellpadding="1" cellspacing="0">
					<tr>
						<td><?php echo JText::_('COM_AZMAILER_LOCATION_COUNTRY'); ?>:</td>
						<td><select style="min-width:200px;" size="1" class="inputbox" id="nls_country_id"
						            name="nls_country_id"></select></td>
					</tr>
					<tr>
						<td><?php echo JText::_('COM_AZMAILER_LOCATION_REGION'); ?>:</td>
						<td><select style="min-width:200px;" size="1" class="inputbox" id="nls_region_id"
						            name="nls_region_id"></select></select></td>
					</tr>
					<tr>
						<td><?php echo JText::_('COM_AZMAILER_LOCATION_PROVINCE'); ?>:</td>
						<td><select style="min-width:200px;" size="1" class="inputbox" id="nls_province_id"
						            name="nls_province_id"></select></td>
					</tr>
				</table>
			</fieldset>


			<h3 class="ui-widget-header behaveSel"
			    style="height:25px;"><?php echo JText::_('COM_AZMAILER_NEWSLETTER_TIT_SEL_BEHAVIOUR'); ?>
				<?php //GEO/CAT SELECTION BEHAVIOUR SELECTORS
				echo '<input type="radio" name="nlcb" id="nlcb_plusor" value="PLUSOR" /><label for="nlcb_plusor">PLUSOR</label>';
				echo '<input type="radio" name="nlcb" id="nlcb_plusand" value="PLUSAND" /><label for="nlcb_plusand">PLUSAND</label>';
				echo '<input type="radio" name="nlcb" id="nlcb_minusand" value="MINUSAND" /><label for="nlcb_minusand">MINUSAND</label>';
				?>
			</h3>

			<div style="padding:10px;" name="catSelBehaviourDesc"><span></span></div>


			<h3 class="ui-widget-header"
			    style="height:25px;"><?php echo JText::_('COM_AZMAILER_NEWSLETTER_TIT_SEL_CATEGORY'); ?>
				<a name="cat_selection_reset"
				   style="float:right;width:16px; height:16px;cursor:pointer; margin:5px 2px 2px 2px;"
				   class="ui-icon ui-icon-cancel"
				   title="<?php echo JText::_('COM_AZMAILER_NEWSLETTER_TIT_UNSELECT_ALL_CATEGORIES'); ?>"></a>
			</h3>

			<div class="ui-widget-content">
				<div id="NL_CATEGORY_SELECTORS">
					<?php
					/*these checkbox fields will be checked/unchecked by js getSendtoData */
					echo AZMailerCategoryHelper::getCheckboxHtmlForSelectionCategory(1);
					echo AZMailerCategoryHelper::getCheckboxHtmlForSelectionCategory(2);
					echo AZMailerCategoryHelper::getCheckboxHtmlForSelectionCategory(3);
					echo AZMailerCategoryHelper::getCheckboxHtmlForSelectionCategory(4);
					echo AZMailerCategoryHelper::getCheckboxHtmlForSelectionCategory(5);
					?>
				</div>
			</div>
		</div>
		<div class="ui-widget-content halfsize righty">
			<h3 class="ui-widget-header"
			    style="height:25px;"><?php echo JText::_('COM_AZMAILER_NEWSLETTER_TIT_ADDITIONAL_RECIPIENTS'); ?>
				<a name="xls_upload" style="float:right;width:16px; height:16px;cursor:pointer; margin:2px;"
				   class="ui-icon ui-icon-circle-plus"
				   title="<?php echo JText::_('COM_AZMAILER_NEWSLETTER_TIT_UPLOAD_XLS'); ?>"></a>
				<a name="additional_cnt_remove" rel="all"
				   style="float:right;width:16px; height:16px;cursor:pointer; margin:2px;"
				   class="ui-icon ui-icon-circle-minus"
				   title="<?php echo JText::_('COM_AZMAILER_NEWSLETTER_TIT_ADDITIONAL_RECIPIENTS_REMOVE_ALL'); ?>"></a>
			</h3>

			<div class="ui-widget-content">
				<ul id="NL_additional"></ul>
			</div>

			<h3 class="ui-widget-header"
			    style="height:25px;"><?php echo JText::_('COM_AZMAILER_NEWSLETTER_TIT_GEO_CAT_RECIPIENTS'); ?></h3>

			<div class="ui-widget-content">
				<ul id="NL_catselects"></ul>
			</div>

			<?php if (false) { ?>
				<h3 class="ui-widget-header" style="height:25px;">SQL Query</h3>
				<div class="ui-widget-content" name="catSelSql" style="padding:5px; font-family: monospace; ">...</div>
			<?php } ?>

		</div>
		<div class="clr"></div>
	</div>


</div>


<div id="mDialog" class="mDialog newsletter" title="dialog">
	<h3 class="title"></h3>

	<p class="text"></p>
</div>

<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm" id="adminForm">
	<input type="hidden" name="option" value="<?php echo $AZMAILER->getOption("com_name"); ?>"/>
	<input type="hidden" name="task" value="<?php echo $AZMAILER->getOption("ctrl.task"); ?>"/>
	<input type="hidden" name="id" value="<?php echo $item->get("id"); ?>"/>
	<input type="hidden" name="nl_create_date" value="<?php echo $item->get("nl_create_date"); ?>"/>
	<input type="hidden" name="nl_template_id" value="<?php echo $item->get("nl_template_id"); ?>"/>
	<input type="hidden" name="nl_title" value="<?php echo $item->get("nl_title"); ?>"/>
	<input type="hidden" name="nl_title_internal" value="<?php echo $item->get("nl_title_internal"); ?>"/>
	<input type="hidden" name="nl_email_from" value="<?php echo $item->get("nl_email_from"); ?>"/>
	<input type="hidden" name="nl_email_from_name" value="<?php echo $item->get("nl_email_from_name"); ?>"/>
	<input type="hidden" name="nl_textversion" value="<?php echo $item->get("nl_textversion"); ?>"/>
	<input type="hidden" name="nl_template_substitutions"
	       value="<?php echo $item->get("nl_template_substitutions"); ?>"/>
	<input type="hidden" name="nl_sendto_selections" value="<?php echo $item->get("nl_sendto_selections"); ?>"/>
	<input type="hidden" name="nl_sendto_additional" value="<?php echo $item->get("nl_sendto_additional"); ?>"/>
	<input type="hidden" name="nl_selectcount" value="<?php echo $item->get("nl_selectcount"); ?>"/>
	<?php echo JHTML::_('form.token'); ?>
</form>


<style type="text/css">
	/*ADDITIONAL CONTACTS*/
	ul#NL_additional, ul#NL_catselects {
		background-color: #D5DDE1;
		list-style-type: none;
		margin: 0;
		padding: 0;
		min-height: 2em;
	}

	ul#NL_additional li, ul#NL_catselects li {
		margin: 0;
		padding: 0;
		font-size: 90%;
		line-height: 16px;
		font-weight: normal;
		border-bottom: 1px solid #9CB1B8;
	}

	ul#NL_additional li div.nla_mail, ul#NL_catselects li div.nla_mail {
		float: left;
		width: 50%;
		border-right: 1px solid #9CB1B8;
		margin-right: 5px;
	}

	fieldset input {
		float: none;
	}
</style>

<script language="javascript" type="text/javascript">
	var com_name = "<?php echo $AZMAILER->getOption("com_name"); ?>";
	var allowed_attachment_extensions = JSON.encode('<?php echo json_encode(AZMailerNewsletterHelper::getAllowedAttachmentExtensions());?>');
	var max_allowed_upload_size = parseInt("<?php echo json_encode(AZMailerNewsletterHelper::getMaxAllowedUploadSizeBytes());?>");
	var controller_name = "newsletter";
	//var template_selector = '<select></select>';
	var my_mail = "<?php echo $myMail; ?>";
	/*
	 current is an array that will hold the reference to the current element being edited
	 */
	var current = [];
	current["el"] = null;
	current["attributes"] = null;
	var is_modified = false;//so we know that you modified NL since last save

	var CSBD = [];//Category Selection Behaviour Descriptions(Translated)
	CSBD["PLUSOR"] = "<?php echo JText::_('COM_AZMAILER_NEWSLETTER_DESC_BEHAVIOUR_PLUSOR'); ?>";
	CSBD["PLUSAND"] = "<?php echo JText::_('COM_AZMAILER_NEWSLETTER_DESC_BEHAVIOUR_PLUSAND'); ?>";
	CSBD["MINUSAND"] = "<?php echo JText::_('COM_AZMAILER_NEWSLETTER_DESC_BEHAVIOUR_MINUSAND'); ?>";

	//TRANSLATED STRINGS FOR JS
	var TRANS = [];
	TRANS["COM_AZMAILER_CANCEL"] = "<?php echo JText::_('COM_AZMAILER_CANCEL'); ?>";
	TRANS["COM_AZMAILER_MODIFY"] = "<?php echo JText::_('COM_AZMAILER_MODIFY'); ?>";
	TRANS["COM_AZMAILER_UPLOAD"] = "<?php echo JText::_('COM_AZMAILER_UPLOAD'); ?>";
	TRANS["COM_AZMAILER_UPLOADING"] = "<?php echo JText::_('COM_AZMAILER_UPLOADING'); ?>";
	TRANS["COM_AZMAILER_ERR_UNKNOWN"] = "<?php echo JText::_('COM_AZMAILER_ERR_UNKNOWN'); ?>";
	TRANS["COM_AZMAILER_NAME"] = "<?php echo JText::_('COM_AZMAILER_NAME'); ?>";
	TRANS["COM_AZMAILER_SIZE"] = "<?php echo JText::_('COM_AZMAILER_SIZE'); ?>";
	TRANS["COM_AZMAILER_TYPE"] = "<?php echo JText::_('COM_AZMAILER_TYPE'); ?>";

	//NL_EDIT_CONTENT.JS
	TRANS["COM_AZMAILER_NEWSLETTER_TITLE"] = "<?php echo JText::_('COM_AZMAILER_NEWSLETTER_TITLE'); ?>";
	TRANS["COM_AZMAILER_NEWSLETTER_TITLE_INTERNAL"] = "<?php echo JText::_('COM_AZMAILER_NEWSLETTER_TITLE_INTERNAL'); ?>";
	TRANS["COM_AZMAILER_NEWSLETTER_SENDER"] = "<?php echo JText::_('COM_AZMAILER_NEWSLETTER_SENDER'); ?>";
	TRANS["COM_AZMAILER_NEWSLETTER_SENDER_NAME"] = "<?php echo JText::_('COM_AZMAILER_NEWSLETTER_SENDER_NAME'); ?>";
	TRANS["COM_AZMAILER_NEWSLETTER_SEND_TEST"] = "<?php echo JText::_('COM_AZMAILER_NEWSLETTER_SEND_TEST'); ?>";
	TRANS["COM_AZMAILER_NEWSLETTER_SEND_TEST_DESC"] = "<?php echo JText::_('COM_AZMAILER_NEWSLETTER_SEND_TEST_DESC'); ?>";
	TRANS["COM_AZMAILER_NEWSLETTER_SEND_TEST_MSG_SENDING"] = "<?php echo JText::_('COM_AZMAILER_NEWSLETTER_SEND_TEST_MSG_SENDING'); ?>";
	TRANS["COM_AZMAILER_NEWSLETTER_SIMPLETEXT_CONFIRM"] = "<?php echo JText::_('COM_AZMAILER_NEWSLETTER_SIMPLETEXT_CONFIRM'); ?>";
	TRANS["COM_AZMAILER_NEWSLETTER_TIT_MOD_TEMPLATE"] = "<?php echo JText::_('COM_AZMAILER_NEWSLETTER_TIT_MOD_TEMPLATE'); ?>";
	TRANS["COM_AZMAILER_NEWSLETTER_DESC_MOD_TEMPLATE"] = "<?php echo JText::_('COM_AZMAILER_NEWSLETTER_DESC_MOD_TEMPLATE'); ?>";
	TRANS["COM_AZMAILER_NEWSLETTER_MSG_MOD_TEMPLATE_NO_CHANGE"] = "<?php echo JText::_('COM_AZMAILER_NEWSLETTER_MSG_MOD_TEMPLATE_NO_CHANGE'); ?>";
	TRANS["COM_AZMAILER_NEWSLETTER_TIT_MOD_SENDER"] = "<?php echo JText::_('COM_AZMAILER_NEWSLETTER_TIT_MOD_SENDER'); ?>";
	TRANS["COM_AZMAILER_NEWSLETTER_TIT_MOD_TITLE"] = "<?php echo JText::_('COM_AZMAILER_NEWSLETTER_TIT_MOD_TITLE'); ?>";
	TRANS["COM_AZMAILER_NEWSLETTER_TIT_UPLOAD_FILE"] = "<?php echo JText::_('COM_AZMAILER_NEWSLETTER_TIT_UPLOAD_FILE'); ?>";
	TRANS["COM_AZMAILER_NEWSLETTER_TIT_MOD_HTML"] = "<?php echo JText::_('COM_AZMAILER_NEWSLETTER_TIT_MOD_HTML'); ?>";
	TRANS["COM_AZMAILER_NEWSLETTER_TIT_MOD_TEXT"] = "<?php echo JText::_('COM_AZMAILER_NEWSLETTER_TIT_MOD_TEXT'); ?>";
	TRANS["COM_AZMAILER_NEWSLETTER_MSG_ELEMENT_NOID"] = "<?php echo JText::_('COM_AZMAILER_NEWSLETTER_MSG_ELEMENT_NOID'); ?>";
	TRANS["COM_AZMAILER_NEWSLETTER_MSG_ELEMENT_NOREL"] = "<?php echo JText::_('COM_AZMAILER_NEWSLETTER_MSG_ELEMENT_NOREL'); ?>";
	TRANS["COM_AZMAILER_NEWSLETTER_LINKED_TO"] = "<?php echo JText::_('COM_AZMAILER_NEWSLETTER_LINKED_TO'); ?>";
	TRANS["COM_AZMAILER_NEWSLETTER_DO_APPLY_MODDED"] = "<?php echo JText::_('COM_AZMAILER_NEWSLETTER_DO_APPLY_MODDED'); ?>";
	TRANS["COM_AZMAILER_NEWSLETTER_DO_APPLY_UNSAVED"] = "<?php echo JText::_('COM_AZMAILER_NEWSLETTER_DO_APPLY_UNSAVED'); ?>";
	TRANS["COM_AZMAILER_NEWSLETTER_FILEUPLOAD_ERR_NOJPG"] = "<?php echo JText::_('COM_AZMAILER_NEWSLETTER_FILEUPLOAD_ERR_NOJPG'); ?>";


	//NL_EDIT_RECEPIENTS.JS
	TRANS["COM_AZMAILER_NEWSLETTER_TIT_UPLOAD_XLS"] = "<?php echo JText::_('COM_AZMAILER_NEWSLETTER_TIT_UPLOAD_XLS'); ?>";
	TRANS["COM_AZMAILER_NEWSLETTER_DESC_UPLOAD_XLS"] = "<?php echo JText::_('COM_AZMAILER_NEWSLETTER_DESC_UPLOAD_XLS'); ?>";
	TRANS["COM_AZMAILER_NEWSLETTER_DEL_CONFIRM_SINGLE_CONTACT"] = "<?php echo JText::_('COM_AZMAILER_NEWSLETTER_DEL_CONFIRM_SINGLE_CONTACT'); ?>";
	TRANS["COM_AZMAILER_NEWSLETTER_DEL_CONFIRM_ALL_CONTACTS"] = "<?php echo JText::_('COM_AZMAILER_NEWSLETTER_DEL_CONFIRM_ALL_CONTACTS'); ?>";

	//NL_ADD_ATTACHMENTS
	TRANS["COM_AZMAILER_NEWSLETTER_TIT_ADD_ATTACHMENT"] = "<?php echo JText::_('COM_AZMAILER_NEWSLETTER_TIT_ADD_ATTACHMENT'); ?>";
	TRANS["COM_AZMAILER_NEWSLETTER_TIT_DESC_ATTACHMENT"] = "<?php echo JText::sprintf('COM_AZMAILER_NEWSLETTER_TIT_DESC_ATTACHMENT', ini_get('upload_max_filesize')); ?>";
	TRANS["COM_AZMAILER_NEWSLETTER_ATTACHMENTS_SAVEFIRST"] = "<?php echo JText::_('COM_AZMAILER_NEWSLETTER_ATTACHMENTS_SAVEFIRST'); ?>";
	TRANS["COM_AZMAILER_NEWSLETTER_NO_ATTACHMENTS"] = "<?php echo JText::_('COM_AZMAILER_NEWSLETTER_NO_ATTACHMENTS'); ?>";
	TRANS["COM_AZMAILER_NEWSLETTER_ATTACHMENTS_ERR_LIST"] = "<?php echo JText::_('COM_AZMAILER_NEWSLETTER_ATTACHMENTS_ERR_LIST'); ?>";
	TRANS["COM_AZMAILER_NEWSLETTER_ATTACHMENTS_OPEN"] = "<?php echo JText::_('COM_AZMAILER_NEWSLETTER_ATTACHMENTS_OPEN'); ?>";
	TRANS["COM_AZMAILER_NEWSLETTER_ATTACHMENTS_DELETE"] = "<?php echo JText::_('COM_AZMAILER_NEWSLETTER_ATTACHMENTS_DELETE'); ?>";
	TRANS["COM_AZMAILER_NEWSLETTER_ATTACHMENTS_DELETE_CONFIRM"] = "<?php echo JText::_('COM_AZMAILER_NEWSLETTER_ATTACHMENTS_DELETE_CONFIRM'); ?>";
	TRANS["COM_AZMAILER_NEWSLETTER_ATTACHMENTS_BAD_EXTENSION"] = "<?php echo JText::_('COM_AZMAILER_NEWSLETTER_ATTACHMENTS_BAD_EXTENSION'); ?>";
	TRANS["COM_AZMAILER_NEWSLETTER_ATTACHMENTS_BAD_FILESIZE"] = "<?php echo JText::_('COM_AZMAILER_NEWSLETTER_ATTACHMENTS_BAD_FILESIZE'); ?>";
	TRANS["COM_AZMAILER_NEWSLETTER_ATTACHMENTS_UPLOADED"] = "<?php echo JText::_('COM_AZMAILER_NEWSLETTER_ATTACHMENTS_UPLOADED'); ?>";
	TRANS["COM_AZMAILER_NEWSLETTER_ATTACHMENTS_TIT_UPLOAD_ERROR"] = "<?php echo JText::_('COM_AZMAILER_NEWSLETTER_ATTACHMENTS_TIT_UPLOAD_ERROR'); ?>";
	TRANS["COM_AZMAILER_NEWSLETTER_ATTACHMENTS_BTN_UPLOAD_ANOTHER"] = "<?php echo JText::_('COM_AZMAILER_NEWSLETTER_ATTACHMENTS_UPLOADED'); ?>";


	//INITIALIZE
	jQuery(document).ready(function ($) {

		$("#tabs").tabs({
			cookie: {name: "newsletter_edit", expires: 1},
			show: function (ev, ui) {
				if ($("#tabs").tabs("option", "selected") == 0) {
					updateNLIframeHeight();
				}
			}
		});

		$("#mDialog").dialog({
			width: 600,
			autoOpen: false,
			modal: true,
			closeOnEscape: false,
			draggable: true,
			resizable: false
		});

		$(".ui-dialog.ui-widget a.ui-dialog-titlebar-close").hide();//kill top right close button

		refreshNewsletterContent();
		refreshSendtoOptions();
	});


	//-----------------------------------------------------COMMON AJAX DATA INTERPRETER
	function elaborateJsonResponse(data, showErrors, getFullResponse) {
		var errors = false;
		var answer = false;
		try {
			var jsonObj = (typeof data != "object" ? JSON.parse(data) : data);
			errors = jsonObj.errors;
			if (getFullResponse && getFullResponse === true) {
				answer = jsonObj;
			} else {
				answer = jsonObj.result;
			}
		} catch (e) {
			errors = "Unable to parse string!\n" + e;
		}
		if (showErrors && errors.length > 0) {
			alert("ERROR!\n" + errors);
		}
		return (answer);
	}


	//---------------------------------------------------------OTHER COMMON FUNCTIONS
	function getHumanReadableFileSize(fSize) {
		if (fSize > 1024 * 1024) {
			fileSizeString = (Math.round(fSize * 100 / (1024 * 1024)) / 100).toString() + 'MB';
		} else {
			fileSizeString = (Math.round(fSize * 100 / 1024) / 100).toString() + 'KB';
		}
		return (fileSizeString);
	}


	Joomla.submitbutton = function (pressbutton) {
		// do field validation
		if (pressbutton != "newsletter.display") {
			//TEXT VERSION OF NEWSLETTER(TEXTAREA->FORM)
			jQuery("form[name=adminForm] input[name=nl_textversion]").val(jQuery("textarea[name=nltxt]").val());
		}
		Joomla.submitform(pressbutton);
	}

</script>