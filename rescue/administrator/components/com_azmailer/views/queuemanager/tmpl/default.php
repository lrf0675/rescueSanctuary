<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
JHtml::_('behavior.tooltip');
global $AZMAILER;
use AZMailer\Entities\AZMailerQueueItem;
use AZMailer\Helpers\AZMailerAdminInterfaceHelper;
use AZMailer\Helpers\AZMailerDateHelper;
use AZMailer\Helpers\AZMailerQueuemanagerHelper;

/** @var \JRegistry|\Joomla\Registry\Registry - AZMailer Settings params */
//$params = $this->state->get('params');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));

$MQS = AZMailerQueuemanagerHelper::getMailQueueState(true);//true is for forcing count of unsent mails

$secondsPassedSinceLastExecutionTime = AZMailerDateHelper::getSecondsSince($MQS->last_updated_date);
$formattedSPSLET = 'More than a day!';
if ($secondsPassedSinceLastExecutionTime < (60 * 60 * 24)) {
	$formattedSPSLET = gmdate("H:i:s", AZMailerDateHelper::getSecondsSince($MQS->last_updated_date));
}

//TYPE FILTER
$lst = AZMailerQueuemanagerHelper::getSelectOptions_Type("---" . \JText::_("COM_AZMAILER_MQM_TIT_TYPE") . "---");
$lstdef = $this->state->get('filter.type_sel');
$lstdef = ($lstdef ? $lstdef : 0);
$SB_TYPE = JHtml::_('select.genericlist', $lst, 'filter_type_sel', 'class="inputbox filter" size="1" onchange="document.adminForm.submit();"', 'id', 'data', $lstdef);

//PRIORITY FILTER
$lst = AZMailerQueuemanagerHelper::getSelectOptions_Priority("---" . \JText::_("COM_AZMAILER_MQM_TIT_PRIORITY") . "---");
$lstdef = $this->state->get('filter.priority_sel');
$lstdef = ($lstdef ? $lstdef : 0);
$SB_PRIORITY = JHtml::_('select.genericlist', $lst, 'filter_priority_sel', 'class="inputbox filter" size="1" onchange="document.adminForm.submit();"', 'id', 'data', $lstdef);

//STATE FILTER
$lst = AZMailerQueuemanagerHelper::getSelectOptions_State("---" . \JText::_("COM_AZMAILER_MQM_TIT_MQI_STATE") . "---");
$lstdef = $this->state->get('filter.state_sel');
$lstdef = ($lstdef ? $lstdef : 0);
$SB_STATE = JHtml::_('select.genericlist', $lst, 'filter_state_sel', 'class="inputbox filter" size="1" onchange="document.adminForm.submit();"', 'id', 'data', $lstdef);

?>
<?php echo AZMailerAdminInterfaceHelper::displaySubmenu(); ?>
<div class="ui-widget ui-state-highlight ">
	<table class="mqstate">
		<tr class="names">
			<td><?php echo JText::_("COM_AZMAILER_MQM_MQSTATE_ENABLED"); ?></td>
			<td><?php echo JText::_("COM_AZMAILER_MQM_MQSTATE_INUSE"); ?></td>
			<td><?php echo JText::_("COM_AZMAILER_MQM_MQSTATE_LAST_UPDATE"); ?></td>
			<td><?php echo JText::_("COM_AZMAILER_MQM_MQSTATE_NUM_SENT"); ?></td>
			<td><?php echo JText::_("COM_AZMAILER_MQM_MQSTATE_NUM_UNSENT"); ?></td>
		</tr>
		<tr class="values">
			<td><?php echo($MQS->enabled == 1 ? '<div class="queueState enabled">' . JText::_("COM_AZMAILER_YES") . '</div>' : '<div class="queueState disabled">' . JText::_("COM_AZMAILER_NO") . '</div>'); ?></td>
			<td><?php echo($MQS->blocked == 1 ? JText::_("COM_AZMAILER_YES") . "(" . AZMailerDateHelper::getSecondsSince($MQS->blocked_date) . "s)" : JText::_("COM_AZMAILER_NO")); ?></td>
			<td><?php echo AZMailerDateHelper::convertToHumanReadableFormat($MQS->last_updated_date, "d/m/Y G:i.s") . '(' . $formattedSPSLET . ')'; ?></td>
			<td><?php echo $MQS->sent_count; ?></td>
			<td><?php echo $MQS->unsent_count; ?></td>
		</tr>
	</table>
</div>
<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm" id="adminForm">
	<fieldset id="filter-bar">
		<div class="filter-search fltlft">
			<input type="text" name="filter_search" id="filter_search"
			       value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
			       title="<?php echo JText::_('COM_AZMAILER_SEARCH'); ?>"
			       placeholder="<?php echo JText::_('COM_AZMAILER_SEARCH'); ?>"/>
			<button type="submit"><?php echo JText::_('COM_AZMAILER_SEARCH'); ?></button>
		</div>
		<div class="filter-select fltrt">
			<?php echo $SB_TYPE; ?>
			<?php echo $SB_PRIORITY; ?>
			<?php echo $SB_STATE; ?>
		</div>
	</fieldset>

	<table class="adminlist table table-striped table-bordered table-hover">
		<thead>
		<tr>
			<th style="width:1%;"><input type="checkbox" name="checkall-toggle" value=""
			                             title="<?php echo JText::_('COM_AZMAILER_CHECK_ALL'); ?>"
			                             onclick="Joomla.checkAll(this)"/></th>
			<th style="width:90px;"><?php echo JHTML::_('grid.sort', 'COM_AZMAILER_MQM_TIT_INSERT_DATE', 'mq_date', $listDirn, $listOrder); ?></th>
			<th style="width:80px;"><?php echo JHTML::_('grid.sort', 'COM_AZMAILER_MQM_TIT_TYPE', 'mq_type', $listDirn, $listOrder); ?></th>
			<th style="width:60px;"><?php echo JHTML::_('grid.sort', 'COM_AZMAILER_MQM_TIT_PRIORITY', 'mq_priority', $listDirn, $listOrder); ?></th>
			<th style=""><?php echo JHTML::_('grid.sort', 'COM_AZMAILER_MQM_TIT_SENDER', 'mq_from', $listDirn, $listOrder); ?></th>
			<th style=""><?php echo JHTML::_('grid.sort', 'COM_AZMAILER_MQM_TIT_RECIEVER', 'mq_to', $listDirn, $listOrder); ?></th>
			<th style=""><?php echo JHTML::_('grid.sort', 'COM_AZMAILER_MQM_TIT_SUBJECT', 'mq_subject', $listDirn, $listOrder); ?></th>
			<th style="width:60px;"><?php echo JHTML::_('grid.sort', 'COM_AZMAILER_MQM_TIT_NUM_TRIES', 'mq_send_attempt_count', $listDirn, $listOrder); ?></th>
			<th style="width:80px;"><?php echo JHTML::_('grid.sort', 'COM_AZMAILER_MQM_TIT_SEND_DATE', 'mq_last_send_attempt_date', $listDirn, $listOrder); ?></th>
			<th style="width:25px;"><?php echo JText::_('COM_AZMAILER_MQM_TIT_SEND_STATE'); ?></th>
			<th style="width:25px;"><?php echo JText::_('COM_AZMAILER_MQM_TIT_READ_STATE'); ?></th>
			<th style="width:25px;"><?php echo JText::_('COM_AZMAILER_OPERATIONS'); ?></th>
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
			/** @var $item  AZMailer\Entities\AZMailerQueueItem */
			$checkbox = JHtml::_('grid.id', $i, $item->get("id"));

			$lastSendAttemptDate = ($item->get("mq_last_send_attempt_date") ? AZMailerDateHelper::convertToHumanReadableFormat($item->get("mq_last_send_attempt_date"), "d/m/Y G:i.s") : JText::_("COM_AZMAILER_MQM_MQI_SEND_STATE_UNSENT"));

			$sendState = $item->getState();
			$msClass = '';
			$msTitle = '';
			if ($sendState == AZMailerQueueItem::$STATE_SENT) {//SENT
				$msClass = 'done';
				$msTitle = JText::_('COM_AZMAILER_MQM_MQI_SEND_STATE_OK');
			} else if ($sendState == AZMailerQueueItem::$STATE_FAILED) {//PERMANENT FAIL
				$msClass = 'remove';
				$msTitle = JText::_('COM_AZMAILER_MQM_MQI_SEND_STATE_FAILED');
			} else if ($sendState == AZMailerQueueItem::$STATE_UNSENT) {//UNSENT
				$msClass = 'wait';
				$msTitle = JText::_('COM_AZMAILER_MQM_MQI_SEND_STATE_UNSENT');
			} else if ($sendState == AZMailerQueueItem::$STATE_REQUEUED) {//REQUEUED - SENT AT LEAST ONCE - BUT FAILED
				$msClass = 'alert';
				$msTitle = JText::_('COM_AZMAILER_MQM_MQI_SEND_STATE_REQUEUED');
			}
			$MQ_STATE_S = '<span style="display:block; width:16px; height:16px;" class="icon-16-' . $msClass . '" title="' . $msTitle . '"></span>';

			//ADD LINK TO SHOW LOG INFO ON UNSENT/FAILED MAIL QUEUE ITEM
			$sendLog = $item->get("mq_last_send_attempt_log");
			if (!empty($sendLog)) {
				$MQ_STATE_S = '<a class="fancy log" rel="' . $item->get("id") . '" style="cursor:pointer;">' . $MQ_STATE_S . '</a>';
			}


			$mrClass = 'off';
			$mrTitle = JText::_("COM_AZMAILER_MQM_MQI_READ_STATE_NO");
			if ($item->get("mq_has_been_read") == 1) {//READ
				$mrClass = 'on';
				$mrTitle = JText::_("COM_AZMAILER_MQM_MQI_READ_STATE_YES");
			}
			$MQ_STATE_R = '<span style="display:block; width:16px; height:16px;" class="icon-16-' . $mrClass . '" title="' . $mrTitle . '"></span>';


			$OPERATIONS = '';
			$OPERATIONS .= '<a class="fancy preview" rel="' . $item->get("id") . '" style="cursor:pointer; float:right;" title="' . JText::_("COM_AZMAILER_MQM_MQI_SHOW_MESSAGE") . '"><span class="ui-icon ui-icon-search"></span></a>';


			?>
			<tr>
				<td><?php echo $checkbox; ?></td>
				<td><?php echo AZMailerDateHelper::convertToHumanReadableFormat($item->get("mq_date"), "d/m/Y G:i.s"); ?></td>
				<td><?php echo $item->get("mq_type"); ?></td>
				<td style="text-align: center;"><?php echo $item->get("mq_priority"); ?></td>
				<td><?php echo $item->getCombinedSender(); ?></td>
				<td><?php echo $item->get("mq_to"); ?></td>
				<td><?php echo $item->get("mq_subject"); ?></td>
				<td style="text-align: center;"><?php echo $item->get("mq_send_attempt_count"); ?></td>
				<td><?php echo $lastSendAttemptDate; ?></td>
				<td><?php echo $MQ_STATE_S; ?></td>
				<td><?php echo $MQ_STATE_R; ?></td>
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

		$("a.fancy.log").click(function () {
			var mqiid = $(this).attr("rel");
			jQuery.fancybox({
				type: 'ajax',
				href: 'index.php?option=<?php echo $AZMAILER->getOption("com_name"); ?>&task=queuemanager.getQueueItemLogs&format=raw&mqiid=' + mqiid
			});
		});

		$("a.fancy.preview").click(function () {
			var mqiid = $(this).attr("rel");
			jQuery.fancybox({
				width: 1000,
				height: 600,
				type: 'iframe',
				href: 'index.php?option=<?php echo $AZMAILER->getOption("com_name"); ?>&task=queuemanager.previewQueueItem&format=raw&mqiid=' + mqiid
			});
		});

		$("div.queueState.enabled").click(function () {
			if (confirm("<?php echo \JText::_("COM_AZMAILER_MQM_CONFIRM_DISABLE"); ?>")) {
				$.post("index.php", {
						option: $("form#adminForm input[name=option]").val(),
						task: "queuemanager.disableMailQueue",
						format: "raw"
					},
					function (data) {
						$("form#adminForm").submit();
					}
				);
			}
		});

		$("div.queueState.disabled").click(function () {
			if (confirm("<?php echo \JText::_("COM_AZMAILER_MQM_CONFIRM_ENABLE"); ?>")) {
				$.post("index.php", {
						option: $("form#adminForm input[name=option]").val(),
						task: "queuemanager.enableMailQueue",
						format: "raw"
					},
					function (data) {
						$("form#adminForm").submit();
					}
				);
			}
		});


	});
	/*
	 Joomla.submitbutton = function (pressbutton) {
	 //REMOVE
	 if (pressbutton == 'subscriber.delete') {
	 if (!confirm("< ? php echo JText::_("COM_AZMAILER_NEWSLETTER_DEL_CONFIRM"); ?>")) {
	 return(false);
	 }
	 }
	 Joomla.submitform( pressbutton );
	 }*/
</script>