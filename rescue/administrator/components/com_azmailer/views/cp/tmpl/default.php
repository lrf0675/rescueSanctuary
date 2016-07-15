<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
jimport('joomla.html.pane');
JHtml::_('behavior.tooltip');
global $AZMAILER;
use AZMailer\Helpers\AZMailerAdminInterfaceHelper;
use AZMailer\Helpers\AZMailerDateHelper;
use AZMailer\Helpers\AZMailerQueuemanagerHelper;

?>
<?php echo AZMailerAdminInterfaceHelper::displaySubmenu(); ?>
<div style="width:100%;">
	<div id="cpanel" style="float:left; width:44%;">
		<?php foreach ($this->cpbuttons as &$cpbutton): ?>
			<div class="icon">
				<a href="<?php echo $cpbutton["link"]; ?>" title="<?php echo $cpbutton["title"]; ?>">
					<img src="<?php echo $cpbutton["icon"]; ?>">
					<span><?php echo $cpbutton["title"]; ?></span>
				</a>
			</div>
		<?php endforeach; ?>
	</div>
	<div style="float:right; width:55%;">
		<h3><?php echo JText::_("COM_AZMAILER_CP_INFO"); ?></h3>
		<?php
		$output = '';
		$output .= '<table class="adminlist table table-striped table-bordered table-hover" style="border:1px solid #ababab;" cellpaggind="2" cellspacing="0">';

		//AZMailer Information
		$k = 0;
		foreach ($this->cpinfo as &$cpi) {
			$output .= '<tr class="row' . $k = 1 - $k . '">';
			$output .= '<td>' . $cpi["key"] . '</td>';
			$output .= '<td>' . $cpi["value"] . '</td>';
			$output .= '</tr>';
		}

		//AZMailer Mail Queue Status
		$MQS = AZMailerQueuemanagerHelper::getMailQueueState();
		$secondsPassedSinceLastExecutionTime = AZMailerDateHelper::getSecondsSince($MQS->last_updated_date);
		$formattedSPSLET = 'More than a day! Cron Execution not working!';
		$isWorking = false;
		if ($secondsPassedSinceLastExecutionTime < (60 * 60 * 24)) {
			$formattedSPSLET = gmdate("H:i:s", AZMailerDateHelper::getSecondsSince($MQS->last_updated_date));
			$isWorking = true;
		}
		$mqState = '<div class="queueState ' . ($isWorking == 1 ? "enabled" : "disabled") . '" style="line-height:28px;padding-left:4px;cursor:auto;">';
		$mqState .= ($MQS->enabled == 1 ? \JText::_("COM_AZMAILER_YES") : \JText::_("COM_AZMAILER_NO"));
		$mqState .= ' - ' . JText::_("COM_AZMAILER_MQM_MQSTATE_LAST_UPDATE") . ": " . AZMailerDateHelper::convertToHumanReadableFormat($MQS->last_updated_date, "d/m/Y G:i.s");
		$mqState .= '(' . $formattedSPSLET . ')';
		$mqState .= '</div>';
		$output .= '<tr class="row' . $k = 1 - $k . '">';
		$output .= '<td>' . \JText::_("COM_AZMAILER_MQM_MQSTATE") . " " . \JText::_("COM_AZMAILER_MQM_MQSTATE_ENABLED") . '</td>';
		$output .= '<td>' . $mqState . '</td>';
		$output .= '</tr>';

		$output .= '</table>';
		echo $output;
		?>
	</div>
</div>

<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm" id="adminForm">
	<input type="hidden" name="option" value="<?php echo $AZMAILER->getOption("com_name"); ?>"/>
	<input type="hidden" name="task" value="<?php echo $AZMAILER->getOption("ctrl.task"); ?>"/>
	<input type="hidden" name="boxchecked" value="0"/>
	<?php echo JHtml::_('form.token'); ?>
</form>
