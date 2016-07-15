<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
use AZMailer\Helpers\AZMailerAdminInterfaceHelper;
use AZMailer\Helpers\AZMailerEditorHelper;

JHtml::_('behavior.tooltip');
global $AZMAILER;
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));

?>

<?php echo AZMailerAdminInterfaceHelper::displaySubmenu(); ?>
<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm" id="adminForm">

	<fieldset id="filter-bar">
		<div class="filter-search fltlft">
			<label class="filter-search-lbl" for="filter_search"><?php echo JText::_('COM_AZMAILER_SEARCH'); ?></label>
			<input type="text" name="filter_search" id="filter_search"
			       value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
			       title="<?php echo JText::_('COM_AZMAILER_SEARCH'); ?>"/>
			<button type="submit"><?php echo JText::_('COM_AZMAILER_SEARCH'); ?></button>
		</div>
		<div class="filter-select fltrt"></div>
	</fieldset>

	<table class="adminlist table table-striped table-bordered table-hover">
		<thead>
		<th width="1%"><input type="checkbox" name="checkall-toggle" value=""
		                      title="<?php echo JText::_('COM_AZMAILER_CHECK_ALL'); ?>"
		                      onclick="Joomla.checkAll(this)"/>
		</th>
		<th width=""><?php echo JHtml::_('grid.sort', 'COM_AZMAILER_NAME', 'a.tpl_name', $listDirn, $listOrder); ?></th>
		<th width=""><?php echo JHtml::_('grid.sort', 'COM_AZMAILER_TEMPLATE_CODE', 'a.tpl_code', $listDirn, $listOrder); ?></th>
		<th width=""><?php echo JHtml::_('grid.sort', 'COM_AZMAILER_TEMPLATE_TYPE', 'a.tpl_type', $listDirn, $listOrder); ?></th>
		<th width=""><?php echo JHtml::_('grid.sort', 'COM_AZMAILER_TEMPLATE_DEFAULT_TITLE', 'a.tpl_title', $listDirn, $listOrder); ?></th>
		<th width=""><?php echo JText::_('COM_AZMAILER_TEMPLATE_TEXT'); ?></th>
		<th width="30"><?php echo JText::_('COM_AZMAILER_OPERATIONS'); ?></th>
		</thead>
		<tfoot>
		<tr>
			<td colspan="100"><?php echo $this->pagination->getListFooter(); ?></td>
		</tr>
		</tfoot>
		<tbody>
		<?php
		foreach ($this->items as $i => $item):
			$checkbox = JHtml::_('grid.id', $i, $item->id);

			$ELP = new stdClass();
			$ELP->title = JText::_('COM_AZMAILER_TEMPLATE_EDITOR_TITLE') . ': ' . $item->tpl_name;
			$ELP->parent_type = "template";
			$ELP->parent_id = $item->id;
			$ELP->return_uri = base64_encode('index.php?option=' . $AZMAILER->getOption("com_name") . '&task=' . $AZMAILER->getOption("ctrl.task"));
			$EDITOR_LINK = '<a href="' . AZMailerEditorHelper::getEditorLink($ELP, true) . '" title="' . JText::_('COM_AZMAILER_TEMPLATE_EDITOR_TITLE') . '">' . $item->tpl_name . '</a>';

			$TPLTEXT = strip_tags($item->htmlblob);
			$TPLTEXT = (strlen($TPLTEXT) > 50 ? substr($TPLTEXT, 0, 50) . "..." : $TPLTEXT);

			$EDITTPLSETTINGS = '<a style="float:right;" title="' . JText::_('COM_AZMAILER_TEMPLATE_MOD_TIT') . '" href="' . JRoute::_('index.php?option=' . $AZMAILER->getOption("com_name") . '&task=' . $AZMAILER->getOption("controller") . '.edit&cid=' . $item->id) . '"><span class="ui-icon ui-icon-wrench"></span></a>';

			?>
			<tr class="row<?php echo $i % 2; ?>">
				<td><?php echo $checkbox; ?></td>
				<td><?php echo $EDITOR_LINK; ?></td>
				<td><?php echo $item->tpl_code; ?></td>
				<td><?php echo $item->tpl_type; ?></td>
				<td><?php echo $item->tpl_title; ?></td>
				<td><?php echo $TPLTEXT; ?></td>
				<td><?php echo $EDITTPLSETTINGS; ?></td>
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
	<?php echo JHtml::_('form.token'); ?>
</form>

<script language="javascript" type="text/javascript">
	Joomla.submitbutton = function (pressbutton) {
		if (pressbutton == 'template.delete') {
			if (!confirm("<?php echo JText::_('COM_AZMAILER_TEMPLATE_DEL_CONFIRM'); ?>")) {
				return (false);
			}
		}
		Joomla.submitform(pressbutton);
	}
</script>