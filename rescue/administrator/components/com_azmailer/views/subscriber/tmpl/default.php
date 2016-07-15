<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
//use AZMailer\Helpers\AZMailerEditorHelper;
use AZMailer\Entities\AZMailerSubscriber;
use AZMailer\Helpers\AZMailerAdminInterfaceHelper;
use AZMailer\Helpers\AZMailerCategoryHelper;
use AZMailer\Helpers\AZMailerLocationHelper;

JHtml::_('behavior.tooltip');
/** @var $AZMAILER \AZMailer\AZMailerCore */
global $AZMAILER;


/** @var $params \Joomla\Registry\Registry AZMailer Settings params */
$params = $this->state->get('params');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));

//LOCATION FILTERS - COUNTRY
$lst = AZMailerLocationHelper::getSelectOptions_Countries("---" . \JText::_("COM_AZMAILER_LOCATION_COUNTRY") . "---");
$lstdef = $this->state->get('filter.country_sel');
$lstdef = ($lstdef ? $lstdef : 0);
$SB_COUNTRY = JHtml::_('select.genericlist', $lst, 'filter_country_sel', 'class="inputbox filter" size="1" onchange="jQuery(\'select#filter_region_sel\').val(0);jQuery(\'select#filter_province_sel\').val(0);document.adminForm.submit();"', 'id', 'data', $lstdef);

//LOCATION FILTERS - REGION
$lst = AZMailerLocationHelper::getSelectOptions_Regions("---" . \JText::_("COM_AZMAILER_LOCATION_REGION") . "---", $lstdef);
$lstdef = $this->state->get('filter.region_sel');
$lstdef = ($lstdef ? $lstdef : 0);
$SB_REGION = JHtml::_('select.genericlist', $lst, 'filter_region_sel', 'class="inputbox filter" size="1" onchange="jQuery(\'select#filter_province_sel\').val(0);document.adminForm.submit();"', 'id', 'data', $lstdef);

//LOCATION FILTERS - PROVINCE
$lst = AZMailerLocationHelper::getSelectOptions_Provinces("---" . \JText::_("COM_AZMAILER_LOCATION_PROVINCE") . "---", $lstdef);
$lstdef = $this->state->get('filter.province_sel');
$lstdef = ($lstdef ? $lstdef : 0);
$SB_PROVINCE = JHtml::_('select.genericlist', $lst, 'filter_province_sel', 'class="inputbox filter" size="1" onchange="document.adminForm.submit();"', 'id', 'data', $lstdef);

//CATYEGORY FILTERS
$C1N = $params->get("category_name_1");
$C2N = $params->get("category_name_2");
$C3N = $params->get("category_name_3");
$C4N = $params->get("category_name_4");
$C5N = $params->get("category_name_5");
for ($CN = 1; $CN <= 5; $CN++) {
	$lst = AZMailerCategoryHelper::getSelectOptions_CatItems($CN, "---" . ${"C" . $CN . "N"} . "---");
	$lstdef = $this->state->get('filter.cat_sel_' . $CN);
	$lstdef = ($lstdef ? $lstdef : 0);
	$SB_CAT[$CN] = JHtml::_('select.genericlist', $lst, 'filter_cat_sel_' . $CN, 'class="inputbox filter" size="1" onchange="document.adminForm.submit();"', 'id', 'data', $lstdef);
}

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
			<?php echo $SB_COUNTRY; ?>
			<?php echo $SB_REGION; ?>
			<?php echo $SB_PROVINCE; ?>
			<?php echo $SB_CAT[1]; ?>
			<?php echo $SB_CAT[2]; ?>
			<?php echo $SB_CAT[3]; ?>
			<?php echo $SB_CAT[4]; ?>
			<?php echo $SB_CAT[5]; ?>
		</div>
	</fieldset>

	<table class="adminlist table table-striped table-bordered table-hover">
		<thead>
		<tr>
			<th style="width:1%;"><input type="checkbox" name="checkall-toggle" value=""
			                             title="<?php echo JText::_('COM_AZMAILER_CHECK_ALL'); ?>"
			                             onclick="Joomla.checkAll(this)"/></th>
			<th style="width:20px;"><?php echo JText::_("COM_AZMAILER_SUBSCR_TIT_BLACKLISTED"); ?></th>
			<th style="width:20px;"><?php echo JText::_("COM_AZMAILER_SUBSCR_TIT_VALID"); ?></th>
			<th style="width:20px;"><?php echo JText::_("COM_AZMAILER_SUBSCR_TIT_WWW"); ?></th>
			<th style="width:auto;"><?php echo JHTML::_('grid.sort', 'COM_AZMAILER_SUBSCR_TIT_EMAIL', 'nls_email', $listDirn, $listOrder); ?></th>
			<th style="width:auto;"><?php echo JHTML::_('grid.sort', 'COM_AZMAILER_NAME', 'nls_lastname', $listDirn, $listOrder); ?></th>
			<th style="width:auto;"><?php echo JHTML::_('grid.sort', 'COM_AZMAILER_LOCATION_COUNTRY', 'country_name', $listDirn, $listOrder); ?></th>
			<th style="width:auto;"><?php echo JHTML::_('grid.sort', 'COM_AZMAILER_LOCATION_REGION', 'region_name', $listDirn, $listOrder); ?></th>
			<th style="width:auto;"><?php echo JHTML::_('grid.sort', 'COM_AZMAILER_LOCATION_PROVINCE', 'province_name', $listDirn, $listOrder); ?></th>
			<th style="width:auto;"><?php echo $C1N; ?></th>
			<th style="width:auto;"><?php echo $C2N; ?></th>
			<th style="width:auto;"><?php echo $C3N; ?></th>
			<th style="width:auto;"><?php echo $C4N; ?></th>
			<th style="width:auto;"><?php echo $C5N; ?></th>
			<th style="width:30px;"><?php echo JText::_('COM_AZMAILER_OPERATIONS'); ?></th>
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

			//BLACKLISTED?
			$M_BL = '';
			if ($item->getIsBlacklisted()) {
				$mblClass = 'remove';
				$mblTitle = JText::_("COM_AZMAILER_SUBSCR_DESC_BLACKLISTED");
				$M_BL = '<span style="display:block; width:16px; height:16px;" class="icon-16-' . $mblClass . '" title="' . $mblTitle . '"></span>';
			}

			//MAIL VALIDITY
			$M_VV = '';
			$mvClass = 'question';
			$mvTitle = JText::_("COM_AZMAILER_SUBSCR_MAIL_VALIDITY_NOCTRL");
			if ($item->getMailValidity() == AZMailerSubscriber::$VALIDITY_VALID) {
				$mvClass = 'on';
				$mvTitle = JText::_("COM_AZMAILER_SUBSCR_MAIL_VALIDITY_VALID");
			} else if ($item->getMailValidity() == AZMailerSubscriber::$VALIDITY_INVALID) {
				$mvClass = 'off';
				$mvTitle = JText::_("COM_AZMAILER_SUBSCR_MAIL_VALIDITY_INVALID");
			}
			$mvTitle = '(' . $item->get("nls_mail_validation_code") . ') ' . $mvTitle;
			$M_VV = '<span style="display:block; width:16px; height:16px;" class="icon-16-' . $mvClass . '" title="' . $mvTitle . '"></span>';
			//ADD LINK TO SHOW LOG INFO ON FAILED CHECKED MAILS
			if ($item->get("nls_mail_validation_log") != "") {
				$M_VV = '<a name="showmaillog" rel="' . $item->get("id") . '" style="cursor:pointer;">' . $M_VV . '</a>';
			}

			//guess domain from email and set link for it
			$WWW = '';
			if (($domain = $item->guessDomainNameFromMail())) {
				$WWW = '<a href="http://www.' . $domain . '" target="_blank">'
					. '<span style="display:block; width:16px; height:16px;" class="icon-16-domain"></span>'
					. '</a>';
			}


			$editUri = JRoute::_('index.php?option=' . $AZMAILER->getOption("com_name") . '&task=' . $AZMAILER->getOption("controller") . '.edit&cid=' . $item->get("id"));
			$editLnk = '<a href="' . $editUri . '">' . $item->get("nls_email") . '</a>';


			$OPS = '&nbsp;';


			?>
			<tr>
				<td><?php echo $checkbox; ?></td>
				<td><?php echo $M_BL; ?></td>
				<td><?php echo $M_VV; ?></td>
				<td><?php echo $WWW; ?></td>
				<td><?php echo $editLnk; ?></td>
				<td><?php echo $item->getFullName(); ?></td>
				<td><?php echo $item->getCountryName(); ?></td>
				<td><?php echo $item->getRegionName(); ?></td>
				<td><?php echo $item->getProvinceName(); ?></td>
				<td><?php echo $item->getCategoryNamesList(1); ?></td>
				<td><?php echo $item->getCategoryNamesList(2); ?></td>
				<td><?php echo $item->getCategoryNamesList(3); ?></td>
				<td><?php echo $item->getCategoryNamesList(4); ?></td>
				<td><?php echo $item->getCategoryNamesList(5); ?></td>
				<td><?php echo $OPS; ?></td>
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

		$("a[name=showmaillog]").click(function () {
			var nlsid = $(this).attr("rel");
			jQuery.fancybox({
				href: 'index.php?option=<?php echo $AZMAILER->getOption("com_name"); ?>&task=subscriber.showFailedCheckLog&format=raw&nlsid=' + nlsid
			});
		});

	});

	Joomla.submitbutton = function (pressbutton) {

		//REMOVE
		if (pressbutton == 'subscriber.delete') {
			if (!confirm("<?php echo JText::_("COM_AZMAILER_SUBSCR_CONFIRM_DELETE"); ?>")) {
				return (false);
			}
		}

		//CHECK MAILS
		if (pressbutton == 'checkmail') {
			jQuery.fancybox({
				href: 'index.php?option=<?php echo $AZMAILER->getOption("com_name"); ?>&task=subscriber.doMailChecks&tmpl=component',
				type: 'iframe',
				width: 800,
				height: 600,
				modal: true,
				onClosed: function () {
					window.location.href = 'index.php?option=<?php $AZMAILER->getOption("com_name"); ?>&task=subscriber.display';
				}
			});
			return (false);
		}

		Joomla.submitform(pressbutton);
	}
</script>