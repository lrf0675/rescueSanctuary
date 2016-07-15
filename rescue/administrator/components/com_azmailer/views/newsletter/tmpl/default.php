<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
JHtml::_('behavior.tooltip');
global $AZMAILER;

use AZMailer\Helpers\AZMailerAdminInterfaceHelper;
use AZMailer\Helpers\AZMailerDateHelper;

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));

?>

<?php echo AZMailerAdminInterfaceHelper::displaySubmenu(); ?>

<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm" id="adminForm">
	<fieldset id="filter-bar">
		<div class="filter-search fltlft">
			<input type="text" name="filter_search" id="filter_search"
			       value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
			       title="<?php echo JText::_('COM_AZMAILER_SEARCH'); ?>"
			       placeholder="<?php echo JText::_('COM_AZMAILER_SEARCH'); ?>"/>
			<button class="btn" type="submit"><?php echo JText::_('COM_AZMAILER_SEARCH'); ?></button>
		</div>
		<div class="filter-select fltrt">
		</div>
	</fieldset>

	<table class="adminlist table table-striped table-bordered table-hover">
		<thead>
		<tr>
			<th style="width:1%;"><input type="checkbox" name="checkall-toggle" value=""
			                             title="<?php echo JText::_('COM_AZMAILER_CHECK_ALL'); ?>"
			                             onclick="Joomla.checkAll(this)"/></th>
			<th style=""><?php echo JText::_("COM_AZMAILER_NEWSLETTER_TITLE"); ?></th>
			<th style=""><?php echo JText::_("COM_AZMAILER_NEWSLETTER_TITLE_INTERNAL"); ?></th>
			<th style=""><?php echo JText::_("COM_AZMAILER_NEWSLETTER_SENDER"); ?></th>
			<th style="width:80px;"><?php echo JText::_("COM_AZMAILER_NEWSLETTER_CREATION_DATE"); ?></th>
			<th style="width:80px;"><?php echo JText::_("COM_AZMAILER_NEWSLETTER_SEND_DATE"); ?></th>
			<th style="width:40px;"><?php echo JText::_("COM_AZMAILER_NEWSLETTER_NUM_SELECTED"); ?></th>
			<th style="width:40px;"><?php echo JText::_("COM_AZMAILER_NEWSLETTER_NUM_SENDS"); ?></th>
			<th style="width:40px;"><?php echo JText::_('COM_AZMAILER_OPERATIONS'); ?></th>
		</tr>
		</thead>
		<tfoot>
		<tr>
			<td colspan="100"><?php echo $this->pagination->getListFooter(); ?></td>
		</tr>
		</tfoot>
		<tbody>
		<?php
		foreach ($this->items as $i => $item):
			/** @var $item  \AZMailer\Entities\AZMailerSubscriber */
			$checkbox = JHtml::_('grid.id', $i, $item->get("id"));
			$creationDate = AZMailerDateHelper::convertToHumanReadableFormat($item->get("nl_create_date"));
			$sendDate = ($item->get("nl_send_date") ? AZMailerDateHelper::convertToHumanReadableFormat($item->get("nl_send_date")) : JText::_("COM_AZMAILER_NEWSLETTER_UNSENT"));


			$OPERATIONS = '';
			$OPERATIONS .= '<a class="fancy preview" rel="' . $item->get("id") . '" style="cursor:pointer; float:right;" title="' . JText::_('COM_AZMAILER_NEWSLETTER_DO_VIEW') . '" ><span class="ui-icon ui-icon-search"></span></a>';

			if ($item->get("nl_send_date") == 0 && $item->get("nl_selectcount") > 0) {
				$OPERATIONS .= '<a style="float:right;" class="nlsend" title="' . \JText::_('COM_AZMAILER_NEWSLETTER_DO_SEND') . '" href="index.php?option=' . $AZMAILER->getOption("com_name") . '&task=newsletter.send&newsletter_id=' . $item->get("id") . '"><span class="ui-icon ui-icon-mail-closed"></span></a>';
			} else if ($item->get("nl_send_date") == 0 && $item->get("nl_selectcount") == 0) {
				//not yet sent but no recipients have been set - so it cannot be sent
				$OPERATIONS .= '<a style="float:right;" class="nlsend_norcp" title="' . \JText::_('COM_AZMAILER_NEWSLETTER_DO_SEND_NORCP') . '" ><span class="ui-icon ui-state-disabled ui-icon-mail-closed">x</span></a>';
			}

			//EDIT LINK - if a newsletter has already been sent it cannot be edited anymore
			$editLnk = $item->get("nl_title");
			if ($item->get("nl_send_date") == 0) {
				$editUri = JRoute::_('index.php?option=' . $AZMAILER->getOption("com_name") . '&task=' . $AZMAILER->getOption("controller") . '.edit&cid=' . $item->get("id"));
				$editLnk = '<a href="' . $editUri . '">' . $item->get("nl_title") . '</a>';
			}


			?>
			<tr>
				<td><?php echo $checkbox; ?></td>
				<td><?php echo $editLnk; ?></td>
				<td><?php echo $item->get("nl_title_internal"); ?></td>
				<td><?php echo $item->get("nl_email_from"); ?></td>
				<td><?php echo $creationDate; ?></td>
				<td><?php echo $sendDate; ?></td>
				<td><?php echo $item->get("nl_selectcount"); ?></td>
				<td><?php echo $item->get("nl_sendcount"); ?></td>
				<td><?php echo $OPERATIONS; ?></td>
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
	jQuery(document).ready(function ($) {

		$("a.fancy.preview").click(function () {
			var nlid = $(this).attr("rel");
			jQuery.fancybox({
				type: 'iframe',
				width: 1000,
				height: 600,
				href: 'index.php?option=<?php echo $AZMAILER->getOption("com_name"); ?>&task=newsletter.previewNewsletter&format=raw&nlid=' + nlid
			});
		});

		$("a.nlsend").click(function () {
			if (!confirm("<?php echo \JText::_("COM_AZMAILER_NEWSLETTER_SEND_CONFIRM"); ?>")) {
				return (false);
			}
		});
	});

	Joomla.submitbutton = function (pressbutton) {
		//REMOVE
		if (pressbutton == 'subscriber.delete') {
			if (!confirm("<?php echo JText::_("COM_AZMAILER_NEWSLETTER_DEL_CONFIRM"); ?>")) {
				return (false);
			}
		}
		Joomla.submitform(pressbutton);
	}
</script>