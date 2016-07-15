<?php
namespace AZMailer\Entities;

use AZMailer\Helpers\AZMailerCategoryHelper;
use AZMailer\Helpers\AZMailerDateHelper;
use AZMailer\Helpers\AZMailerLocationHelper;
use AZMailer\Helpers\AZMailerSubscriberHelper;

/**
 * @package    AZMailer
 * @subpackage Entities
 * @author     Adam Jakab {@link http://www.alfazeta.com}
 * @author     Created on 09-Feb-2013
 * @license    GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');

/**
 * Class AZMailerSubscriber
 * @package AZMailer\Entities
 */
class AZMailerSubscriber extends AZMailerEntity {
	public static $VALIDITY_INVALID = 0;
	public static $VALIDITY_VALID = 1;
	public static $VALIDITY_UNCONTROLLED = 2;
	private $locationData;

	/**
	 * @param array|\stdClass $entityData
	 * @param array|\stdClass $entityOptions
	 */
	function __construct($entityData = null, $entityOptions = null) {
		parent::__construct($entityData, $entityOptions);
		$this->checkSetupDefaultValues();
	}

	private function checkSetupDefaultValues() {
		if (!$this->get("nls_subscribe_date")) $this->set("nls_subscribe_date", AZMailerDateHelper::now());
		if (!$this->get("nls_ip")) $this->set("nls_ip", (isset($_SERVER) && isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : "0.0.0.0"));
		//todo: we need default country definition in location manager
		if (!$this->get("nls_country_id")) $this->set("nls_country_id", "1");
		if (!$this->get("nls_region_id")) $this->set("nls_region_id", "0");
		if (!$this->get("nls_province_id")) $this->set("nls_province_id", "0");
		if (!$this->get("nls_cat_1")) $this->set("nls_cat_1", json_encode(AZMailerCategoryHelper::getDefaultOptionsArrayForCategory(1)));
		if (!$this->get("nls_cat_2")) $this->set("nls_cat_2", json_encode(AZMailerCategoryHelper::getDefaultOptionsArrayForCategory(2)));
		if (!$this->get("nls_cat_3")) $this->set("nls_cat_3", json_encode(AZMailerCategoryHelper::getDefaultOptionsArrayForCategory(3)));
		if (!$this->get("nls_cat_4")) $this->set("nls_cat_4", json_encode(AZMailerCategoryHelper::getDefaultOptionsArrayForCategory(4)));
		if (!$this->get("nls_cat_5")) $this->set("nls_cat_5", json_encode(AZMailerCategoryHelper::getDefaultOptionsArrayForCategory(5)));
	}

	/** This is really checkup - returns false if supplied constructor params are invalid
	 *
	 * @param bool  $isnew
	 * @param array $userOrig
	 * @return bool
	 */
	public function setup($isnew = false, $userOrig = null) {
		$answer = $this->_setup($isnew, $userOrig);
		return ($answer);
	}

	/**
	 * This is to account for differently formatted joomla user registration data array, to check and to sanitize data before save
	 * @param boolean $isnew - at user data save we are passed this by J! to know if user is new or just modified
	 * @param array   $userOrig - user data before modification
	 * @return bool
	 */
	private function _setup($isnew = false, $userOrig = null) {

		if (isset($userOrig)) {
			//the data array passed to the constructor was really that of J! user registration and is full of data we don't need
			//we need to get rid of some - at least id
			$this->data->id = null;
		}

		//EMAIL
		$this->data->nls_email = strtolower($this->data->nls_email ? trim(strip_tags($this->data->nls_email)) : trim(strip_tags($this->data->email)));
		if (!AZMailerSubscriberHelper::checkIfEmailSyntaxIsValid($this->data->nls_email)) {
			return (false);
		}

		//TODO: what about new registrations???
		if (!$isnew && isset($userOrig)) {//this is a user data modification
			if (!empty($userOrig['email']) && $userOrig['email'] != $this->data->nls_email) {//user has changed e-mail
				if (!AZMailerSubscriberHelper::checkIfNLSMailIsAvailable($this->data->nls_email)) {
					return (false);//this mail is already registered - bail out
				}
			}
		}

		//NAME
		if (isset($this->data->name)) {
			$tmp = explode(" ", $this->data->name);
			if (is_array($tmp) && count($tmp)) {
				$this->data->nls_firstname = implode("", array_splice($tmp, 0, 1));
				$this->data->nls_lastname = implode(" ", $tmp);
			} else {
				$this->data->nls_firstname = $this->data->name;
				$this->data->nls_lastname = '';
			}
		}
		$this->data->nls_firstname = ucwords(strtolower(trim($this->data->nls_firstname)));
		$this->data->nls_lastname = ucwords(strtolower(trim($this->data->nls_lastname)));
		if (empty($this->data->nls_firstname) && empty($this->data->nls_lastname)) {
			return (false);
		}
		return (true);
	}

	/**
	 * @return string
	 */
	public function getFullName() {
		$answer = $this->get("nls_firstname") . ' ' . $this->get("nls_lastname");
		return ($answer);
	}

	/**
	 * @return bool
	 */
	public function getIsBlacklisted() {
		return ((int)$this->get("nls_blacklisted") == 1);
	}

	/**
	 * @return int
	 */
	public function getMailValidity() {
		$answer = self::$VALIDITY_UNCONTROLLED;
		$MVC = (int)$this->get("nls_mail_validation_code");
		if ($MVC == 250) {
			$answer = self::$VALIDITY_VALID;
		} else if ($MVC != 0 && $MVC != 421) {
			$answer = self::$VALIDITY_INVALID;
		}
		return ($answer);
	}

	/**
	 * Guess domain name from e-mail
	 * @return mixed false or non-free domain name
	 */
	public function guessDomainNameFromMail() {
		global $AZMAILER;
		$answer = false;
		$FREEMAILDOMAINS = json_decode($AZMAILER->getOption("freemail_domains"));
		$FMA = explode(",", $FREEMAILDOMAINS);
		list($username, $domain) = explode('@', $this->get("nls_email"));
		//todo: we need specific loop and preg_match for this so we can use regexp in config
		if (!in_array($domain, $FMA)) {
			$answer = $domain;
		}
		return ($answer);
	}

	/**
	 * @return string
	 */
	public function getCountryName() {
		$this->getLocationData();
		return ((isset($this->locationData["country"]->country_name) ? $this->locationData["country"]->country_name : ""));
	}

	//todo: rename this! - a getter with no return value is not a getter
	private function getLocationData() {
		if (!$this->locationData) {
			$this->locationData = AZMailerLocationHelper::getCountryRegionProvince($this->get("nls_country_id"), $this->get("nls_region_id"), $this->get("nls_province_id"));
		}
	}

	/**
	 * @return string
	 */
	public function getRegionName() {
		$this->getLocationData();
		return ((isset($this->locationData["region"]->region_name) ? $this->locationData["region"]->region_name : ""));
	}


	//------------------------------------------------PRIVATE
	/**
	 * @return string
	 */
	public function getProvinceName() {
		$this->getLocationData();
		return ((isset($this->locationData["province"]->province_name) ? $this->locationData["province"]->province_name : ""));
	}

	/**
	 * @param integer $categoryNumber
	 * @return string
	 */
	public function getCategoryNamesList($categoryNumber = null) {
		return (AZMailerCategoryHelper::getCategoryItemsHumanReadableList($this->get("nls_cat_" . $categoryNumber)));
	}

	/**
	 * Save Subscriber to database
	 * @return bool
	 */
	public function sync() {
		$answer = false;
		/** @var \JTable $table */
		$table = \JTable::getInstance('azmailer_subscriber', 'Table');
		if ($table->save($this->data)) {
			$db = $table->getDbo();
			$this->set("id", $db->insertid());
			$answer = true;
		}
		return ($answer);
	}
}