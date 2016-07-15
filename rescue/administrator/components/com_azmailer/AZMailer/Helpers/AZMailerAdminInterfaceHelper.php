<?php
namespace AZMailer\Helpers;
/**
 * @package    AZMailer
 * @subpackage Helpers
 * @author     Adam Jakab {@link http://www.alfazeta.com}
 * @author     Created on 09-Feb-2013
 * @license    GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');

/**
 * AZMailer Helper Class
 *
 * @author jackisback
 */
class AZMailerAdminInterfaceHelper {
	/**
	 * Adds buttons to toolbar
	 *
	 * @param    array    array of arrays with the following values in this order:
	 * @string    $acl        The Acl action to control
	 * string    $task        The task to perform in the form of controllerName.controllerMethod
	 * string    $icon        The image to display.
	 * string    $title        The title of the button
	 * bool    $listSelect    True if required to check that a standard list item is checked.
	 */
	public static function addButtonsToToolBar($buttons = array()) {
		//global $AZMAILER;
		if ($buttons && is_array($buttons) && count($buttons)) {
			$canDo = self::getActions();
			foreach ($buttons as &$button) {
				if ($canDo->get($button[0])) {
					\JToolBarHelper::custom($button[1], $button[2], $button[2], $button[3], (isset($button[4]) && $button[4] === true));
				}
			}
		}
	}

	/**
	 * @param int $messageId
	 * @return \JObject
	 */
	public static function getActions($messageId = 0) {
		$user = \JFactory::getUser();
		$result = new \JObject;

		if (empty($messageId)) {
			$assetName = 'com_azmailer';
		} else {
			$assetName = 'com_azmailer.message.' . (int)$messageId;
		}

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.delete'
		);

		foreach ($actions as $action) {
			$result->set($action, $user->authorise($action, $assetName));
		}

		return $result;
	}

	/**
	 * Configure and return the rendered submenu bar
	 */
	public static function displaySubmenu() {
		/** @var $AZMAILER \AZMailer\AZMailerCore */
		global $AZMAILER;
		$ctrl = $AZMAILER->getOption('controller');
		$linkBase = 'index.php?option=' . $AZMAILER->getOption("com_name");
		$menu = AZMailerButtonToolbarHelper::getInstance('submenu');
		$menu->appendButton(\JText::_('COM_AZMAILER_SUBMENU_CP'), $linkBase, ($ctrl == 'cp'));
		$menu->appendButton(\JText::_('COM_AZMAILER_SUBMENU_NEWSLETTER'), $linkBase . '&task=newsletter.display', ($ctrl == 'newsletter'));
		$menu->appendButton(\JText::_('COM_AZMAILER_SUBMENU_NLSUBSCRIBER'), $linkBase . '&task=subscriber.display', ($ctrl == 'subscriber'));
		$menu->appendButton(\JText::_('COM_AZMAILER_SUBMENU_MAILQUEUEMANAGER'), $linkBase . '&task=queuemanager.display', ($ctrl == 'queuemanager'));
		$menu->appendButton(\JText::_('COM_AZMAILER_SUBMENU_STATS'), $linkBase . '&task=statistics.display', ($ctrl == 'statistics'));
		$menu->appendButton(\JText::_('COM_AZMAILER_SUBMENU_CATEGORY'), $linkBase . '&task=category.display', ($ctrl == 'category'));
		$menu->appendButton(\JText::_('COM_AZMAILER_SUBMENU_LOCATION'), $linkBase . '&task=location.display', ($ctrl == 'location'));
		$menu->appendButton(\JText::_('COM_AZMAILER_SUBMENU_TEMPLATE'), $linkBase . '&task=template.display', ($ctrl == 'template'));
		$menu->appendButton(\JText::_('COM_AZMAILER_SUBMENU_SETTINGS'), $linkBase . '&task=settings.display', ($ctrl == 'settings'));
		return ($menu->render());
	}

	/**
	 * @param string $title
	 * @param string $icon
	 */
	public static function setHeaderTitle($title = 'AZMailer', $icon = 'azmailer') {
		$document = \JFactory::getDocument();
		$document->setTitle($title);
		\JToolBarHelper::title($title, $icon);
	}



	/*-----------------------------------------------------------------------HTML---------*/

	/**
	 * @param null   $caption
	 * @param null   $name
	 * @param string $default
	 * @param int    $size
	 * @param int    $maxlength
	 * @param bool   $disabled
	 * @param bool   $readonly
	 * @param string $type
	 * @param string $customField
	 * @return string
	 */
	public static function getInputFileldRow($caption = null, $name = null, $default = '', $size = 40, $maxlength = 128, $disabled = false, $readonly = false, $type = 'text', $customField = '') {
		$answer = '';
		if ($name) {
			$readonly = ($readonly ? ' readonly="readonly"' : '');
			$disabled = ($disabled ? ' disabled="disabled"' : '');
			$label = '<label for="' . $name . '">' . $caption . '</label>';
			$errorSpan = '<span id="err_' . $name . '" class="jqerror"></span>';
			if ($type != 'custom') {
				$field = '<input name="' . $name . '" id="' . $name . '" type="' . $type . '" size="' . $size . '" maxlength="' . $maxlength . '" value="' . $default . '"' . $readonly . $disabled . '/>';
			} else {
				$field = $customField;
			}
			$answer = self::showData($label, $field . $errorSpan);
		}
		return ($answer);
	}

	/**
	 * @param      $label
	 * @param      $data
	 * @param null $atag_mask
	 * @param bool $forceShow
	 * @return string
	 */
	public static function showData($label, $data, $atag_mask = null, $forceShow = false) {
		$answer = "";
		if (!empty($data) || $forceShow) {
			$answer .= '<tr>';
			$answer .= '<td class="data_name">' . $label . '</td>';
			$answer .= '<td class="data_val">';
			if (is_null($atag_mask)) {
				$answer .= $data;
			} else {
				$answer .= sprintf($atag_mask, $data) . $data . '</a>';
			}
			$answer .= '</td>';
			$answer .= '</tr>';
		}

		if (!empty($answer)) {
			$answer .= "\n";
		}
		return ($answer);
	}

	/**
	 * GENERIC YES/NO OPTIONS
	 * @param bool $zeroOption
	 * @return array
	 */
	public static function getSelectOptions_YesNo($zeroOption = false) {
		$lst = array();
		if ($zeroOption !== false) {
			$lst[] = \JHTML::_('select.option', '0', \JText::_($zeroOption), 'id', 'data');
		}
		$lst[] = \JHTML::_('select.option', 'Y', \JText::_("COM_AZMAILER_YES"), 'id', 'data');
		$lst[] = \JHTML::_('select.option', 'N', \JText::_("COM_AZMAILER_NO"), 'id', 'data');
		return ($lst);
	}


	/*------------------------------------------------------------------------------------------------------------Header inclusions*/
	public static function setHeaderIncludes() {
		/** @var $AZMAILER \AZMailer\AZMailerCore */
		global $AZMAILER;
		if ($AZMAILER->getOption('com_location') == "backend") {
			//ADD CSS
			self::addAdditionalHeaderIncludes("css", "/assets/css/azmailer.css");
			self::addAdditionalHeaderIncludes("css", "/assets/css/icons.css");
			self::addAdditionalHeaderIncludes("css", "/assets/js/jqueryui10/jquery-ui.min.css");
			self::addAdditionalHeaderIncludes("css", "/assets/js/fancybox/jquery.fancybox.css");
			//ADD SPECIFIC LIBRARY SUPPORT
			self::getJQueryLibrarySupport();
			//ADD JS
			self::addAdditionalHeaderIncludes("js", "/assets/js/fancybox/jquery.fancybox.pack.js");
			self::addAdditionalHeaderIncludes("js", "/assets/js/jqueryui10/jquery-ui.min.js");
			self::addAdditionalHeaderIncludes("js", "/assets/js/jquery-cookie.js");
			self::addAdditionalHeaderIncludes("js", "/assets/js/jquery.json-2.3.min.js");
			self::addAdditionalHeaderIncludes("js", "/assets/js/jquery.base64.js");
			self::addAdditionalHeaderIncludes("js", "/assets/js/jquery.form.js");
		}
	}

	/**
	 * path is intended from admin-side component root: '/assets/css/azmailer.css'
	 * @param string $type
	 * @param null   $path
	 * @param bool   $forceFirst
	 */
	public static function addAdditionalHeaderIncludes($type, $path, $forceFirst = false) {
		/** @var $AZMAILER \AZMailer\AZMailerCore */
		global $AZMAILER;
		/** @var $document \JDocumentHTML */
		$document = \JFactory::getDocument();
		$inclUrl = $AZMAILER->getOption("com_uri_admin") . $path;
		if ($type == "css") {
			if (!$forceFirst) {
				$document->addStyleSheet($inclUrl);
			} else {
				$head = $document->getHeadData();
				$cssType = array('mime' => 'text/css', 'media' => null, 'attribs' => array());
				$head['styleSheets'] = array_merge(array($inclUrl => $cssType), $head['styleSheets']);
				$document->setHeadData($head);
			}
		} else if ($type == "js") {
			if (!$forceFirst) {
				$document->addScript($inclUrl);
			} else {
				$head = $document->getHeadData();
				$jsType = array('mime' => 'text/javascript', 'defer' => false, 'async' => false);
				$head['scripts'] = array_merge(array($inclUrl => $jsType), $head['scripts']);
				$document->setHeadData($head);
			}
		}
	}

	/**
	 * Add jQuery support
	 * todo: jquery-1.7.1 is way too old - get a newer one
	 */
	public static function getJQueryLibrarySupport() {
		if (IS_J3) {
			\JHtml::_('jquery.framework');
		} else {
			if (\JFactory::getApplication()->get('jquery') !== true) {
				//adding scripts in revesed order
				self::addAdditionalHeaderIncludes("js", "/assets/js/jquery-noconflict.js", true);
				self::addAdditionalHeaderIncludes("js", "/assets/js/jquery-1.7.1.min.js", true);
				\JFactory::getApplication()->set('jquery', true);
			}
		}
	}
}

?>
