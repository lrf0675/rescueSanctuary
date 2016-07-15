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
 * Component Parameters Helper Class
 *
 * @author jackisback
 */
class AZMailerComponentParamHelper {


	/**
	 * @param $name
	 * @param $value
	 * @return bool
	 * types: text, textarea, number, path, list
	 */
	public static function submitParamEditForm($name, $value) {
		$answer = "Unknown Error!";
		if (self::keyExists($name)) {
			$PSA = self::getParametersSetupArray();
			$param = $PSA[$name];
			if (!isset($param["readonly"]) || !$param["readonly"]) {
				if (!$param["required"] || ($value != "" && $value != null)) {
					switch ($param["type"]) {
						case "text":
						case "textarea":
							if (!isset($param["validation"]) || (!empty($param["validation"]) && preg_match($param["validation"], $value))) {
								$answer = self::setParamValue($name, $value);
							} else {
								$answer = "Validation failed! You need to match this: '" . $param["validation"] . "'.";
							}
							break;
						case "number";
							$value = (int)$value;
							if (isset($param["min"]) && $value < $param["min"]) {
								$answer = "Validation failed! Your value must be higher than or equal to: " . $param["min"] . ".";
								break;
							}
							if (isset($param["max"]) && $value > $param["max"]) {
								$answer = "Validation failed! Your value must be lower than or equal to: " . $param["max"] . ".";
								break;
							}
							$answer = self::setParamValue($name, $value);
							break;
						case "list":
							$answer = self::setParamValue($name, $value);
							break;
						default:
							$answer = "Parameter type error! The type of parameter you passed is unknown: '" . $param["type"] . "'.";
							break;
					}
				} else {
					$answer = "Required parameter! You need to enter a value here.";
				}
			} else {
				$answer = "Read-only parameter! You cannot change this one.";
			}
		}
		return ($answer);
	}

	/**
	 * @param string $name
	 * @return bool
	 */
	public static function keyExists($name) {
		$PSA = self::getParametersSetupArray();
		return (array_key_exists($name, $PSA));
	}

	/**
	 * @return array
	 */
	public static function getParametersSetupArray() {
		/** @var \AZMailer\AZMailerCore $AZMAILER */
		global $AZMAILER;
		//if it has been already set up then don't do it again
		if (($PSA = $AZMAILER->getParametersSetupArray()) !== null) {
			return ($PSA);
		}

		$answer = array();
		//---------------------------------------------------------------------------------------------------------------NEWSLETTER
		$answer["mail_default_from"] = array(
			"group" => "newsletter",
			"type" => "text",
			"validation" => "#^[_a-z0-9-]+(\\.[_a-z0-9-]+)*@[a-z0-9-]+(\\.[a-z0-9-]+)*(\\.[a-z]{2,4})$#",
			"default" => "yourmail@yourdomain.com",
			"required" => true,
			"label" => \JText::_("COM_AZMAILER_SETTING_PN_MAIL_DEFAULT_FROM"),
			"description" => \JText::_("COM_AZMAILER_SETTING_PN_MAIL_DEFAULT_FROM_DESC"),
		);
		$answer["mail_default_from_name"] = array(
			"group" => "newsletter",
			"type" => "text",
			"default" => "",
			"required" => false,
			"label" => \JText::_("COM_AZMAILER_SETTING_PN_MAIL_DEFAULT_FROM_NAME"),
			"description" => \JText::_("COM_AZMAILER_SETTING_PN_MAIL_DEFAULT_FROM_NAME_DESC"),
		);
		$answer["nl_removeme_text"] = array(
			"group" => "newsletter",
			"type" => "textarea",
			"default" => "If you do not want to receive other newsletters from us, please click the link below.",
			"required" => true,
			"label" => \JText::_("COM_AZMAILER_SETTING_PN_NL_REMOVEME_TEXT"),
			"description" => \JText::_("COM_AZMAILER_SETTING_PN_NL_REMOVEME_TEXT_DESC"),
		);
		$answer["nl_removeme_html"] = array(
			"group" => "newsletter",
			"type" => "textarea",
			"default" => "I do not want to receive other newsletters.",
			"required" => true,
			"label" => \JText::_("COM_AZMAILER_SETTING_PN_NL_REMOVEME_HTML"),
			"description" => \JText::_("COM_AZMAILER_SETTING_PN_NL_REMOVEME_HTML_DESC"),
		);
		$answer["nl_removeme_linkcolor"] = array(
			"group" => "newsletter",
			"type" => "text",
			"validation" => "@^#[a-f0-9]{6}$@i",
			"default" => "#dadada",
			"required" => true,
			"width" => 100,
			"label" => \JText::_("COM_AZMAILER_SETTING_PN_NL_REMOVEME_LINKCOLOR"),
			"description" => \JText::_("COM_AZMAILER_SETTING_PN_NL_REMOVEME_LINKCOLOR_DESC"),
		);
		$answer["nl_show_max_contacts"] = array(
			"group" => "newsletter",
			"type" => "number",
			"default" => 0,
			"min" => 0,
			"max" => 500,
			"required" => true,
			"width" => 60,
			"label" => \JText::_("COM_AZMAILER_SETTING_PN_NL_SHOW_MAX_CONTACTS"),
			"description" => \JText::_("COM_AZMAILER_SETTING_PN_NL_SHOW_MAX_CONTACTS_DESC"),
		);
		$answer["nl_attachment_allowed_extensions"] = array(
			"group" => "newsletter",
			"type" => "textarea",
			"default" => "pdf, xls, doc, gz, gzip, zip, 7z",
			"required" => false,
			"label" => \JText::_("COM_AZMAILER_ATTACHMENT_ALLOWED_EXTENSIONS"),
			"description" => \JText::_("COM_AZMAILER_ATTACHMENT_ALLOWED_EXTENSIONS_DESC"),
		);
		$answer["nl_max_filesize_to_attach_mb"] = array(
			"group" => "newsletter",
			"type" => "number",
			"default" => 1,
			"min" => 0,
			"max" => 10,
			"required" => true,
			"width" => 60,
			"label" => \JText::_("COM_AZMAILER_SETTING_PN_NL_MAX_ATTACH_FILESIZE"),
			"description" => \JText::_("COM_AZMAILER_SETTING_PN_NL_MAX_ATTACH_FILESIZE_DESC"),
		);

		//---------------------------------------------------------------------------------------------------------------SMTP
		$answer["mail_default_helo"] = array(
			"group" => "smtp",
			"type" => "text",
			"default" => "",
			"required" => false,
			"label" => \JText::_("COM_AZMAILER_SETTING_PN_MAIL_DEFAULT_HELO"),
			"description" => \JText::_("COM_AZMAILER_SETTING_PN_MAIL_DEFAULT_HELO_DESC"),
		);
		$answer["smtp_header_message_id_at"] = array(
			"group" => "smtp",
			"type" => "text",
			"default" => "",
			"required" => false,
			"label" => \JText::_("COM_AZMAILER_SETTING_PN_SMTP_HEADER_MESSAGE_ID_AT"),
			"description" => \JText::_("COM_AZMAILER_SETTING_PN_SMTP_HEADER_MESSAGE_ID_AT_DESC"),
		);
		$answer["smtp_header_x_mailer"] = array(
			"group" => "smtp",
			"type" => "text",
			"default" => "",
			"required" => false,
			"label" => \JText::_("COM_AZMAILER_SETTING_PN_SMTP_HEADER_X_MAILER"),
			"description" => \JText::_("COM_AZMAILER_SETTING_PN_SMTP_HEADER_X_MAILER_DESC"),
		);

		//---------------------------------------------------------------------------------------------------------------MAIL QUEUE
		$answer["mq_manual_mail_check_timeout_ms"] = array(
			"group" => "queue",
			"type" => "number",
			"default" => 10000,
			"min" => 1000,
			"max" => 30000,
			"required" => true,
			"width" => 100,
			"label" => \JText::_("COM_AZMAILER_SETTING_PN_MQ_MANUAL_MAIL_CHECK_TIMEOUT_MS"),
			"description" => \JText::_("COM_AZMAILER_SETTING_PN_MQ_MANUAL_MAIL_CHECK_TIMEOUT_MS_DESC"),
		);
		$answer["mq_purge_sent_items_after_days"] = array(
			"group" => "queue",
			"type" => "number",
			"default" => 15,
			"min" => 1,
			"max" => 365,
			"required" => true,
			"width" => 100,
			"label" => \JText::_("COM_AZMAILER_SETTING_PN_MQ_PURGE_SENT_ITEMS_AFTER_DAYS"),
			"description" => \JText::_("COM_AZMAILER_SETTING_PN_MQ_PURGE_SENT_ITEMS_AFTER_DAYS_DESC"),
		);
		$answer["mq_purge_unsent_items_after_days"] = array(
			"group" => "queue",
			"type" => "number",
			"default" => 30,
			"min" => 1,
			"max" => 365,
			"required" => true,
			"width" => 100,
			"label" => \JText::_("COM_AZMAILER_SETTING_PN_MQ_PURGE_UNSENT_ITEMS_AFTER_DAYS"),
			"description" => \JText::_("COM_AZMAILER_SETTING_PN_MQ_PURGE_UNSENT_ITEMS_AFTER_DAYS_DESC"),
		);
		$answer["mq_max_blocked_time_sec"] = array(
			"group" => "queue",
			"type" => "number",
			"default" => 300,
			"min" => 30,
			"max" => 3600,
			"required" => true,
			"width" => 100,
			"label" => \JText::_("COM_AZMAILER_SETTING_PN_MQ_MAX_BLOCKED_TIME_SEC"),
			"description" => \JText::_("COM_AZMAILER_SETTING_PN_MQ_MAX_BLOCKED_TIME_SEC_DESC"),
		);
		$answer["mq_max_runtime_sec"] = array(
			"group" => "queue",
			"type" => "number",
			"default" => 250,
			"min" => 30,
			"max" => 3600,
			"required" => true,
			"width" => 100,
			"label" => \JText::_("COM_AZMAILER_SETTING_PN_MQ_MAX_RUNTIME_SEC"),
			"description" => \JText::_("COM_AZMAILER_SETTING_PN_MQ_MAX_RUNTIME_SEC_DESC"),
		);
		//PRI-1
		$answer["mq_pri1_attempts_num"] = array(
			"group" => "queue",
			"type" => "number",
			"default" => 2,
			"min" => 1,
			"max" => 30,
			"required" => true,
			"width" => 100,
			"label" => \JText::sprintf("COM_AZMAILER_SETTING_PN_MQ_ATTEMPTS_NUM", 1),
			"description" => \JText::sprintf("COM_AZMAILER_SETTING_PN_MQ_ATTEMPTS_NUM_DESC", 1),
		);
		$answer["mq_pri1_attempts_delay_sec"] = array(
			"group" => "queue",
			"type" => "number",
			"default" => 300,
			"min" => 1,
			"max" => 604800,
			"required" => true,
			"width" => 100,
			"label" => \JText::sprintf("COM_AZMAILER_SETTING_PN_MQ_ATTEMPTS_DELAY_SEC", 1),
			"description" => \JText::sprintf("COM_AZMAILER_SETTING_PN_MQ_ATTEMPTS_DELAY_SEC_DESC", 1),
		);
		//PRI-2
		$answer["mq_pri2_attempts_num"] = array(
			"group" => "queue",
			"type" => "number",
			"default" => 2,
			"min" => 1,
			"max" => 30,
			"required" => true,
			"width" => 100,
			"label" => \JText::sprintf("COM_AZMAILER_SETTING_PN_MQ_ATTEMPTS_NUM", 2),
			"description" => \JText::sprintf("COM_AZMAILER_SETTING_PN_MQ_ATTEMPTS_NUM_DESC", 2),
		);
		$answer["mq_pri2_attempts_delay_sec"] = array(
			"group" => "queue",
			"type" => "number",
			"default" => 900,
			"min" => 1,
			"max" => 604800,
			"required" => true,
			"width" => 100,
			"label" => \JText::sprintf("COM_AZMAILER_SETTING_PN_MQ_ATTEMPTS_DELAY_SEC", 2),
			"description" => \JText::sprintf("COM_AZMAILER_SETTING_PN_MQ_ATTEMPTS_DELAY_SEC_DESC", 2),
		);
		//PRI-3
		$answer["mq_pri3_attempts_num"] = array(
			"group" => "queue",
			"type" => "number",
			"default" => 2,
			"min" => 1,
			"max" => 30,
			"required" => true,
			"width" => 100,
			"label" => \JText::sprintf("COM_AZMAILER_SETTING_PN_MQ_ATTEMPTS_NUM", 3),
			"description" => \JText::sprintf("COM_AZMAILER_SETTING_PN_MQ_ATTEMPTS_NUM_DESC", 3),
		);
		$answer["mq_pri3_attempts_delay_sec"] = array(
			"group" => "queue",
			"type" => "number",
			"default" => 3600,
			"min" => 1,
			"max" => 604800,
			"required" => true,
			"width" => 100,
			"label" => \JText::sprintf("COM_AZMAILER_SETTING_PN_MQ_ATTEMPTS_DELAY_SEC", 3),
			"description" => \JText::sprintf("COM_AZMAILER_SETTING_PN_MQ_ATTEMPTS_DELAY_SEC_DESC", 3),
		);
		//PRI-4
		$answer["mq_pri4_attempts_num"] = array(
			"group" => "queue",
			"type" => "number",
			"default" => 2,
			"min" => 1,
			"max" => 30,
			"required" => true,
			"width" => 100,
			"label" => \JText::sprintf("COM_AZMAILER_SETTING_PN_MQ_ATTEMPTS_NUM", 4),
			"description" => \JText::sprintf("COM_AZMAILER_SETTING_PN_MQ_ATTEMPTS_NUM_DESC", 4),
		);
		$answer["mq_pri4_attempts_delay_sec"] = array(
			"group" => "queue",
			"type" => "number",
			"default" => 10800,
			"min" => 1,
			"max" => 604800,
			"required" => true,
			"width" => 100,
			"label" => \JText::sprintf("COM_AZMAILER_SETTING_PN_MQ_ATTEMPTS_DELAY_SEC", 4),
			"description" => \JText::sprintf("COM_AZMAILER_SETTING_PN_MQ_ATTEMPTS_DELAY_SEC_DESC", 4),
		);
		//PRI-5
		$answer["mq_pri5_attempts_num"] = array(
			"group" => "queue",
			"type" => "number",
			"default" => 3,
			"min" => 1,
			"max" => 30,
			"required" => true,
			"width" => 100,
			"label" => \JText::sprintf("COM_AZMAILER_SETTING_PN_MQ_ATTEMPTS_NUM", 5),
			"description" => \JText::sprintf("COM_AZMAILER_SETTING_PN_MQ_ATTEMPTS_NUM_DESC", 5),
		);
		$answer["mq_pri5_attempts_delay_sec"] = array(
			"group" => "queue",
			"type" => "number",
			"default" => 21600,
			"min" => 1,
			"max" => 604800,
			"required" => true,
			"width" => 100,
			"label" => \JText::sprintf("COM_AZMAILER_SETTING_PN_MQ_ATTEMPTS_DELAY_SEC", 5),
			"description" => \JText::sprintf("COM_AZMAILER_SETTING_PN_MQ_ATTEMPTS_DELAY_SEC_DESC", 5),
		);

		//---------------------------------------------------------------------------------------------------------------OTHER
		$answer["newsletter_cache_image_base"] = array(
			"group" => "other",
			"type" => "path",
			"default" => "media/com_azmailer/cache",
			"required" => true,
			"readonly" => true,
			"label" => \JText::_("COM_AZMAILER_SETTING_PN_NEWSLETTER_CACHE_IMAGE_BASE"),
			"description" => \JText::_("COM_AZMAILER_SETTING_PN_NEWSLETTER_CACHE_IMAGE_BASE_DESC"),
		);
		$answer["newsletter_attachment_base"] = array(
			"group" => "other",
			"type" => "path",
			"default" => "media/com_azmailer/attachments",
			"required" => true,
			"readonly" => true,
			"label" => \JText::_("COM_AZMAILER_SETTING_PN_NEWSLETTER_ATTACHMENT_BASE"),
			"description" => \JText::_("COM_AZMAILER_SETTING_PN_NEWSLETTER_ATTACHMENT_BASE_DESC"),
		);
		$answer["newsletter_http_host"] = array(
			"group" => "other",
			"type" => "text",
			"validation" => "#^(https?:\\/\\/)?([a-z0-9-]*\\.)+([a-z0-9-]*)$#i",/*"#^(https?:\\/\\/)?([\\da-z\\.-]+)\\.([a-z\\.]{2,6})$#i",*/
			"default" => "http://www.yourdomain.com",
			"required" => true,
			"label" => \JText::_("COM_AZMAILER_SETTING_PN_NEWSLETTER_HTTP_HOST"),
			"description" => \JText::_("COM_AZMAILER_SETTING_PN_NEWSLETTER_HTTP_HOST_DESC"),
		);

		$answer["remove_subscriber_on_userdelete"] = array(
			"group" => "other",
			"type" => "list",
			"default" => 0,
			"options" => array(
				0 => \JText::_("COM_AZMAILER_NO"),
				1 => \JText::_("COM_AZMAILER_YES"),
			),
			"required" => true,
			"label" => \JText::_("COM_AZMAILER_SETTING_PN_DEL_SUBSCRIBER_ON_USERDELETE"),
			"description" => \JText::_("COM_AZMAILER_SETTING_PN_DEL_SUBSCRIBER_ON_USERDELETE_DESC"),
		);


		$answer["category_name_1"] = array(
			"group" => "other",
			"type" => "text",
			"default" => "Category 1",
			"required" => true,
			"label" => \JText::sprintf("COM_AZMAILER_SETTING_PN_CATEGORY_NAME", 1),
			"description" => \JText::sprintf("COM_AZMAILER_SETTING_PN_CATEGORY_NAME_DESC", 1),
		);
		$answer["category_name_2"] = array(
			"group" => "other",
			"type" => "text",
			"default" => "Category 2",
			"required" => true,
			"label" => \JText::sprintf("COM_AZMAILER_SETTING_PN_CATEGORY_NAME", 2),
			"description" => \JText::sprintf("COM_AZMAILER_SETTING_PN_CATEGORY_NAME_DESC", 2),
		);
		$answer["category_name_3"] = array(
			"group" => "other",
			"type" => "text",
			"default" => "Category 3",
			"required" => true,
			"label" => \JText::sprintf("COM_AZMAILER_SETTING_PN_CATEGORY_NAME", 3),
			"description" => \JText::sprintf("COM_AZMAILER_SETTING_PN_CATEGORY_NAME_DESC", 3),
		);
		$answer["category_name_4"] = array(
			"group" => "other",
			"type" => "text",
			"default" => "Category 4",
			"required" => true,
			"label" => \JText::sprintf("COM_AZMAILER_SETTING_PN_CATEGORY_NAME", 4),
			"description" => \JText::sprintf("COM_AZMAILER_SETTING_PN_CATEGORY_NAME_DESC", 4),
		);
		$answer["category_name_5"] = array(
			"group" => "other",
			"type" => "text",
			"default" => "Category 5",
			"required" => true,
			"label" => \JText::sprintf("COM_AZMAILER_SETTING_PN_CATEGORY_NAME", 5),
			"description" => \JText::sprintf("COM_AZMAILER_SETTING_PN_CATEGORY_NAME_DESC", 5),
		);
		$answer["freemail_domains"] = array(
			"group" => "other",
			"type" => "textarea",
			"default" => "gmail.com, gmail.it, hotmail.com, msn.com, yahoo.com, libero.it, yahoo.it, alice.it, tiscali.it, tiscalinet.it, hotmail.it, virgilio.it, live.it, tin.it, tim.it, fastwebnet.it, inwind.it, email.it, katamail.com, iol.it,interfree.it, 191.it, pec.it, gigapec.it, legalmail.it, postcert.it, teletu.it, tele2.it, freemail.it",
			"required" => false,
			"height" => 200,
			"label" => \JText::_("COM_AZMAILER_SETTING_PN_FREEMAIL_DOMAINS"),
			"description" => \JText::_("COM_AZMAILER_SETTING_PN_FREEMAIL_DOMAINS_DESC"),
		);

		//---------------------------------------------------------------------------------------------------------------ADVANCED
		$answer["remove_dbtables_on_uninstall"] = array(
			"group" => "advanced",
			"type" => "list",
			"default" => 0,
			"options" => array(
				0 => \JText::_("COM_AZMAILER_NO"),
				1 => \JText::_("COM_AZMAILER_YES"),
			),
			"required" => true,
			"label" => \JText::_("COM_AZMAILER_SETTING_PN_DEL_DB_ON_UNINSTALL"),
			"description" => \JText::_("COM_AZMAILER_SETTING_PN_DEL_DB_ON_UNINSTALL_DESC"),
		);
		$answer["enable_joomla_jmail_override"] = array(
			"group" => "advanced",
			"type" => "list",
			"default" => 0,
			"options" => array(
				0 => \JText::_("COM_AZMAILER_NO"),
				1 => \JText::_("COM_AZMAILER_YES"),
			),
			"required" => true,
			"readonly" => true,
			"label" => \JText::_("COM_AZMAILER_SETTING_PN_ENABLE_JMAIL_OVERRIDE"),
			"description" => \JText::_("COM_AZMAILER_SETTING_PN_ENABLE_JMAIL_OVERRIDE_DESC"),
		);


		return ($answer);
	}

	/**
	 * @param string $name
	 * @param mixed  $value
	 * @return mixed
	 */
	public static function setParamValue($name, $value) {
		$answer = false;
		if (self::keyExists($name)) {
			/** @var \JRegistry|\Joomla\Registry\Registry $params */
			$params = \JComponentHelper::getParams('com_azmailer');
			$params->set($name, $value);
			//save params to db
			$db = \JFactory::getDBO();
			$query = $db->getQuery(true);
			$query->update('#__extensions AS a');
			$query->set('a.params = ' . $db->quote((string)$params));
			$query->where('a.element = "com_azmailer"');
			$db->setQuery($query);
			$db->execute();
			$answer = true;
		}
		return ($answer);
	}

	/**
	 * @param string $name
	 * @return string
	 */
	public static function getParamEditForm($name) {
		$answer = '';
		if (self::keyExists($name)) {
			global $AZMAILER;
			$PSA = self::getParametersSetupArray();
			$param = $PSA[$name];
			//label
			$answer .= '<label for="paramValue">' . $param["label"] . '</label>';
			$val = $AZMAILER->getOption($name);

			if (!isset($param["readonly"]) || !$param["readonly"]) {
				$width = (isset($param["width"]) ? $param["width"] : 570);
				$height = (isset($param["height"]) ? $param["height"] : 100);
				switch ($param["type"]) {
					case "text":
					case "number":
						$answer .= '<input type="text" style="width:' . $width . 'px;" value="' . $val . '" name="paramValue" id="paramValue" />';
						break;
					case "textarea":
						$answer .= '<textarea style="width:' . $width . 'px;height:' . $height . 'px;" name="paramValue" id="paramValue">' . $val . '</textarea>';
						break;
					case "list":
						$answer .= '<select style="width:' . $width . 'px;" name="paramValue" id="paramValue">';
						foreach ($param["options"] as $k => $v) {
							$selected = ($k == $val ? ' selected="selected"' : '');
							$answer .= '<option value="' . $k . '"' . $selected . '>' . $v . '</option>';
						}
						$answer .= '</select>';
				}
			} else {
				$answer .= '<p>READ ONLY: ' . $val . '</p>';
			}
			//description
			$answer .= '<p class="description">'
				. $param["description"]
				. '<br />'
				. (isset($param["required"]) && $param["required"] ? "Required" : "")
				. (isset($param["min"]) ? "&nbsp;Min:" . $param["min"] : "")
				. (isset($param["max"]) ? "&nbsp;Max:" . $param["max"] : "")
				. '</label>';
		}
		return ($answer);
	}

	/**
	 * @param string $name
	 * @param mixed  $value
	 * @return string
	 */
	public static function getParamValueNameFromList($name, $value) {
		$answer = "";
		if (self::keyExists($name)) {
			$PSA = self::getParametersSetupArray();
			if ($PSA[$name]["type"] == "list" && isset($PSA[$name]["options"]) && count($PSA[$name]["options"])) {
				if (isset($PSA[$name]["options"][$value])) {
					$answer = $PSA[$name]["options"][$value];
				}
			}
		}
		return ($answer);
	}

	/**
	 * @param string $group
	 * @return array
	 */
	public static function getParamsInGroup($group) {
		$answer = array();
		$PSA = self::getParametersSetupArray();
		foreach ($PSA as $key => $param) {
			if ($param["group"] == $group) {
				$answer[$key] = $param;
			}
		}
		return ($answer);
	}

	/**
	 * @return array
	 */
	public static function getParamGroups() {
		$answer = array();
		$PSA = self::getParametersSetupArray();
		foreach ($PSA as &$param) {
			if (!in_array($param["group"], $answer)) {
				array_push($answer, $param["group"]);
			}
		}
		return ($answer);
	}

	/**
	 * called by installer script in install/script.php
	 */
	public static function recheckConfigurationAndSetDefaultConfiguration() {
		$PSA = self::getParametersSetupArray();
		foreach ($PSA as $key => $param) {
			self::_checkDefaultConfigurationFor($key, $param);
		}
	}

	/** Set default configuration values defined by parameterSetupArray and sets some special ones
	 * @param $name
	 * @param $param
	 */
	private static function _checkDefaultConfigurationFor($name, $param) {
		$value = self::getParamValue($name);
		if (!$value) {
			//special cases
			switch ($name) {
				case "mail_default_from":
					$user = \JFactory::getUser();
					$value = $user->get("email");
					break;
				case "mail_default_from_name":
					$user = \JFactory::getUser();
					$value = $user->get("name");
					break;
				case "mail_default_helo":
					$value = $_SERVER["HTTP_HOST"];
					break;
				case "newsletter_http_host":
					$value = 'http://' . $_SERVER["HTTP_HOST"];
					break;
			}
			if (!$value) {
				$value = $param["default"];
			}
			//echo "\n<br/>Setting value for $name: '".$value."'";
			self::setParamValue($name, $value);
		}
	}

	/**
	 * @param string $name
	 * @return mixed
	 */
	public static function getParamValue($name) {
		$answer = false;
		if (self::keyExists($name)) {
			$answer = \JComponentHelper::getParams("com_azmailer")->get($name);
		}
		return ($answer);
	}
}