<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
global $AZMAILER;
use AZMailer\Helpers\AZMailerAdminInterfaceHelper;
use AZMailer\Helpers\AZMailerSubscriberHelper;


/** @var \Joomla\Registry\Registry $params - AZMailer Settings params */
$params = $this->state->get('params');

//BUTTONS TO SELECT 5 CATEGORIES
$SHOW_WHAT_BUTTONS = '<ul style="list-style:none;margin:0;padding:0;">';
for ($i = 1; $i <= 5; $i++) {
	$disabled = ($i == $this->state->get('filter.category_id') ? ' disabled="disabled" ' : ' ');
	$onclick = ($i == $this->state->get('filter.category_id') ? ' ' : ' onclick="document.getElementById(\'filter_category_id\').value=' . $i . ';this.form.submit();" ');
	$SWName = $params->get("category_name_" . $i);
	//
	$SHOW_WHAT_BUTTONS .= '<li style="float:left; margin: 0 5px 0 0;">'
		. '<button class="btn btn-info" ' . $disabled . 'value="' . $SWName . '" title="' . $SWName . '"' . $onclick . '>' . $SWName . '</button>'
		. '</li>';
}
$SHOW_WHAT_BUTTONS .= '</ul>';

//CURRENT CATEGORY NAME
$CURRENT_CATNAME = $params->get("category_name_" . $this->state->get('filter.category_id'));


?>
<?php echo AZMailerAdminInterfaceHelper::displaySubmenu(); ?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
	<fieldset id="filter-bar">
		<div class="filter-search fltlft">
			<?php echo JText::_('COM_AZMAILER_CATEGORY_DESC_SEL_CAT'); ?>
			<?php echo $SHOW_WHAT_BUTTONS; ?>
		</div>
		<div class="filter-select fltrt">
			<h2><span class="hasTip"
			          title="<?php echo $CURRENT_CATNAME; ?>::You can change the name of this category in the configuration options"><?php echo $CURRENT_CATNAME; ?></span>
			</h2>
		</div>
	</fieldset>

	<table class="adminlist table table-striped table-bordered table-hover">
		<thead>
		<th><?php echo JText::_('COM_AZMAILER_CATEGORY_TIT_ELEMENT_NAME'); ?></th>
		<th width="30"><?php echo JText::_('COM_AZMAILER_CATEGORY_TIT_ELEMENT_PREDEF'); ?></th>
		<th width="30"><?php echo JText::_('COM_AZMAILER_CATEGORY_TIT_ELEMENT_COUNT'); ?></th>
		<th width="30"><?php echo JText::_('COM_AZMAILER_OPERATIONS'); ?></th>
		</thead>
		<tfoot>
		<tr>
			<td colspan="100"><?php /*echo $this->pagination->getListFooter();*/ ?></td>
		</tr>
		</tfoot>
		<tbody>

		<?php
		foreach ($this->items as $i => $item):

			$ITEMNAME = '<a href="javascript:void(0);" onclick="changeElementName(' . $item->id . ',\'' . base64_encode($item->name) . '\')">' . $item->name . '</a>';

			$defClass = 'off';
			$defTitle = JText::_('COM_AZMAILER_CATEGORY_DESC_PREDEF');
			if ($item->is_default == 1) {
				$defClass = 'on';
			}
			$PREDEF = '<span style="display:block; width:16px; height:16px;" class="icon-16-' . $defClass . '" title="' . $defTitle . '"></span>';
			$PREDEF = '<a onclick="setDefaultOptionOnElement(' . $item->id . ',' . (1 - $item->is_default) . ')" style="cursor:pointer;" title="' . $defTitle . '">' . $PREDEF . '</a>';

			$COUNT = AZMailerSubscriberHelper::countSubscribersByCategoryItem($item->id);

			$DELETE = '&nbsp;';
			if ($COUNT == 0) {
				$DELETE = '<a onclick="deleteElement(' . $item->id . ',\'' . base64_encode($item->name) . '\')" style="float:right; cursor:pointer;" title="' . JText::_('COM_AZMAILER_DELETE') . '"><span class="ui-icon ui-icon-circle-minus"></span></a>';
			}

			?>
			<tr class="row<?php echo $i % 1; ?>" id="element.<?php echo $item->id; ?>">
				<td><strong><?php echo $ITEMNAME; ?></strong></td>
				<td align="center"><?php echo $PREDEF; ?></td>
				<td align="right"><?php echo $COUNT; ?></td>
				<td align="right"><?php echo $DELETE; ?></td>
			</tr>
		<?php
		endforeach;
		?>
		</tbody>
	</table>

	<input type="hidden" name="option" value="<?php echo $AZMAILER->getOption("com_name"); ?>"/>
	<input type="hidden" name="task" value="<?php echo $AZMAILER->getOption("ctrl.task"); ?>"/>
	<input type="hidden" id="filter_category_id" name="filter_category_id"
	       value="<?php echo $this->state->get('filter.category_id'); ?>"/>
	<?php echo JHtml::_('form.token'); ?>
</form>

<div id="mDialog" title="dialog">
	<h3 class="title"></h3>

	<p class="text"></p>
</div>


<script language="javascript" type="text/javascript">
	Joomla.submitbutton = function (pressbutton) {
		if (pressbutton == 'category.new') {
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

		$("table.adminlist tbody").sortable({
			forcePlaceholderSize: true,
			opacity: 0.7,
			helper: function (e, ui) {
				ui.children().each(function () {
					$(this).width($(this).width());
				});
				return ui;
			}
		}).disableSelection();

		$("table.adminlist tbody").bind("sortupdate", function (event, ui) {
			var serialized = $("table.adminlist tbody").sortable("serialize", {expression: new RegExp('(element)\.(.+)')});
			saveOrderedItems(serialized);
		});


	});


	function addNewElement() {
		jQuery('#mDialog').dialog("option", "title", "<?php echo JText::_('COM_AZMAILER_CATEGORY_NEW_ITEM_NAME'); ?>");
		jQuery('#mDialog .title').html("<?php echo JText::_('COM_AZMAILER_CATEGORY_MOD_ITEM_NAME_DESC'); ?>");
		var formhtml = '';
		formhtml += '<input name="cat_name" size="60" maxlength="32" value="" /><br /><br />';
		jQuery('#mDialog .text').html(formhtml);

		jQuery('#mDialog').dialog("option", "buttons", [
				{
					text: "<?php echo JText::_('COM_AZMAILER_ADD'); ?>",
					click: function () {
						var name = jQuery("#mDialog .text input[name=cat_name]").val();
						jQuery.post("index.php", {
								option: jQuery("form#adminForm input[name=option]").val(),
								task: "category.addNew",
								format: "raw",
								cat_index: jQuery("form#adminForm input#filter_category_id").val(),
								name: name
							},
							function (data) {
								elaborateJsonResponse(data, true, "category.display");
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

	function changeElementName(objID, name) {
		jQuery('#mDialog').dialog("option", "title", "<?php echo JText::_('COM_AZMAILER_CATEGORY_MOD_ITEM_NAME'); ?>");
		jQuery('#mDialog .title').html("<?php echo JText::_('COM_AZMAILER_CATEGORY_MOD_ITEM_NAME_DESC'); ?>");
		var formhtml = '';
		formhtml += '<input name="cat_name" size="70" maxlength="32" value="' + jQuery.base64Decode(name) + '" /><br /><br />';
		jQuery('#mDialog .text').html(formhtml);
		jQuery('#mDialog').dialog("option", "buttons", [
				{
					text: "<?php echo JText::_('COM_AZMAILER_MODIFY'); ?>",
					click: function () {
						var name = jQuery("#mDialog .text input[name=cat_name]").val();
						jQuery.post("index.php", {
								option: jQuery("form#adminForm input[name=option]").val(),
								task: "category.changeName",
								format: "raw",
								cat_index: jQuery("form#adminForm input#filter_category_id").val(),
								name: name,
								id: objID
							},
							function (data) {
								elaborateJsonResponse(data, true, "category.display");
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

	function deleteElement(objID, name) {
		if (confirm("<?php echo JText::_('COM_AZMAILER_CATEGORY_DEL_CONFIRM'); ?> " + jQuery.base64Decode(name) + "?")) {
			jQuery.post("index.php", {
					option: jQuery("form#adminForm input[name=option]").val(),
					task: "category.delete",
					format: "raw",
					cat_index: jQuery("form#adminForm input#filter_category_id").val(),
					id: objID
				},
				function (data) {
					elaborateJsonResponse(data, true, "category.display");
				}
			);
		}
	}

	function setDefaultOptionOnElement(objID, defOption) {
		jQuery.post("index.php", {
				option: jQuery("form#adminForm input[name=option]").val(),
				task: "category.setDefaultOption",
				format: "raw",
				cat_index: jQuery("form#adminForm input#filter_category_id").val(),
				id: objID,
				is_default: defOption
			},
			function (data) {
				elaborateJsonResponse(data, true, "category.display");
			}
		);
	}

	function saveOrderedItems(serialized) {
		jQuery.post("index.php", {
				option: jQuery("form#adminForm input[name=option]").val(),
				task: "category.saveOrderedItems",
				format: "raw",
				cat_index: jQuery("form#adminForm input#filter_category_id").val(),
				serialized: serialized
			},
			function (data) {
				elaborateJsonResponse(data, true, "");
			}
		);
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
