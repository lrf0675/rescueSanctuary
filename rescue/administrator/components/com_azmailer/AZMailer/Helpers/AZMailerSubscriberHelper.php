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
 * Subscriber Helper Class
 *
 * @author jackisback
 */
class AZMailerSubscriberHelper {


	/**
	 * very special case when for attachment download only email is supplied - should only be admin
	 * @param $email
	 * @return bool
	 */
	public static function checkIfMailisAdminUser($email) {
		$db = \JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(a.id)');
		$query->from('#__users AS a');
		$query->where('a.email = ' . $db->quote($db->escape(strtolower(trim($email)), true), false));
		$db->setQuery($query);
		return (($db->loadResult() == 1));
	}

	/**
	 * @param string $email
	 * @param integer $id
	 * @return bool
	 */
	public static function checkIfNLSMailIsAvailable($email, $id = 0) {
		$NLS = self::getNewsletterSubscriberByMail($email);
		if ($NLS) {
			if ($NLS->id == $id) {
				$answer = true;
			} else {
				$answer = false;
			}
		} else {
			$answer = true;
		}
		return ($answer);
	}

	/**
	 * @param string $email
	 * @return mixed
	 */
	public static function getNewsletterSubscriberByMail($email) {
		$db = \JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.*');
		$query->from('#__azmailer_subscriber AS a');
		$query->where('a.nls_email = ' . $db->quote($db->escape(strtolower(trim($email)), true), false));
		$db->setQuery($query);
		return ($db->loadObject());
	}

	/**
	 * @param integer $categoryItemId
	 * @return bool|mixed
	 */
	public static function countSubscribersByCategoryItem($categoryItemId) {
		$answer = false;
		$categoryId = AZMailerCategoryHelper::getCategoryIDForItem($categoryItemId);
		if ($categoryId) {
			$db = \JFactory::getDbo();
			$sql = 'SELECT COUNT(*) FROM #__azmailer_subscriber AS a'
				. ' WHERE a.nls_cat_' . $categoryId . ' REGEXP "\"' . $categoryItemId . '\""';
			$db->setQuery($sql);
			$answer = $db->loadResult();
		}
		return ($answer);
	}

	/**
	 * @param integer $country_id
	 * @return mixed
	 */
	public static function countSubscribersInCountry($country_id) {
		$db = \JFactory::getDbo();
		$sql = 'SELECT COUNT(*) FROM #__azmailer_subscriber WHERE nls_country_id = ' . $country_id;
		$db->setQuery($sql);
		return ($db->loadResult());
	}

	/**
	 * @param integer $region_id
	 * @return mixed
	 */
	public static function countSubscribersInRegion($region_id) {
		$db = \JFactory::getDbo();
		$sql = 'SELECT COUNT(*) FROM #__azmailer_subscriber WHERE nls_region_id = ' . $region_id;
		$db->setQuery($sql);
		return ($db->loadResult());
	}

	/**
	 * @param integer $province_id
	 * @return mixed
	 */
	public static function countSubscribersInProvince($province_id) {
		$db = \JFactory::getDbo();
		$sql = 'SELECT COUNT(*) FROM #__azmailer_subscriber WHERE nls_province_id = ' . $province_id;
		$db->setQuery($sql);
		return ($db->loadResult());
	}

	/**
	 * @param array $sheetColumns
	 * @return array
	 */
	public static function getImportColumnsWithIndexes($sheetColumns) {
		$importColumns = self::getImportColumns();
		foreach ($sheetColumns as $i => $sheetColumn) {
			foreach ($importColumns as $importColumnKey => $importColumnVal) {
				if (strtolower($sheetColumn) == strtolower($importColumnKey)) {
					$importColumns[$importColumnKey] = $i;
					break;
				}
			}
		}
		return ($importColumns);
	}

	/**
	 * The column names in the uploaded xls file
	 * @return array
	 */
	public static function getImportColumns() {
		return (array(
			"E-mail" => null,
			"Firstname" => null,
			"Lastname" => null,
			"Country" => null,
			"Region" => null,
			"Province" => null,
			"Category 1" => null,
			"Category 2" => null,
			"Category 3" => null,
			"Category 4" => null,
			"Category 5" => null,
			"Blacklist" => null
		));
	}


	/**
	 * @param \PHPExcel_Worksheet $objWorksheet
	 * @param array $importColumns
	 * @param integer $rowIndex
	 * @param string $columnName
	 * @return bool|mixed|string
	 */
	public static function getImportCellValue($objWorksheet, $importColumns, $rowIndex, $columnName) {
		$answer = false;
		$cellIndex = self::getImportColumnIndexByName($importColumns, $columnName);
		if ($cellIndex !== false) {
			$cell = $objWorksheet->getCellByColumnAndRow($cellIndex, $rowIndex);
			$CV = trim($cell->getValue());
			if ($columnName == "E-mail") {
				$CV_STRICT = preg_replace('#[^(\x20-\x7F)]*#', '', @iconv('Windows-1252', 'ASCII//TRANSLIT', $CV));
				$answer = $CV_STRICT;
			} else if ($columnName == "Blacklist") {
				//will return 0/1 if set or false if empty
				$CV_STRICT = preg_replace('#[^(\x20-\x7F)]*#', '', @iconv('Windows-1252', 'ASCII//TRANSLIT', $CV));
				$answer = (preg_match('/^[y1]$/i', $CV_STRICT) ? "Y" : $answer);
				$answer = (preg_match('/^[n0]$/i', $CV_STRICT) ? "N" : $answer);
			} else {
				$answer = $CV;
			}
		}
		return ($answer);
	}

	/**
	 * @param array $importColumns
	 * @param string $columnName
	 * @return bool
	 */
	public static function getImportColumnIndexByName($importColumns, $columnName) {
		return ((array_key_exists($columnName, $importColumns) ? $importColumns[$columnName] : false));
	}

	/**
	 * will check and elaborate data and
	 * substitute cleaned up checked data on "data" prop
	 * and add "valid" bool property
	 * @param array $imports
	 * @param array $defaultValues
	 * @return array
	 *
	 * defaultValues: Array(
	 * [nls_overwrite_existing] => 3
	 * [nls_blacklisted] => N
	 * [nls_country_id] => 1
	 * [nls_region_id] => 0
	 * [nls_province_id] => 0
	 * [nls_cat_1] => Array([0] => 1)
	 * [nls_cat_2] => Array()
	 * [nls_cat_3] => Array()
	 * [nls_cat_4] => Array()
	 * [nls_cat_5] => Array()
	 * )
	 */
	public static function checkAndCleanUpXlsImportedData($imports, $defaultValues) {
		if (!is_array($imports) || !count($imports)) {
			return $imports;
		};
		foreach ($imports as $import) {
			$import->valid = false;//we will set this to the real res value at the end === $ok
			$import->validationMessage = "Unchecked";
			/** @var \stdClass $originalData */
			$originalData = $import->data;
			$cleandata = new \stdClass();

			//CHECK MAIL
			if (self::checkIfEmailSyntaxIsValid($originalData->nls_email)) {
				$cleandata->nls_email = $originalData->nls_email;
			} else {
				$import->validationMessage = "Invalid E-mail";
				continue;
			}

			//CHECK FIRSTNAME & LASTNAME (we need at least one of them to put it into firstname)
			$firstname = $originalData->nls_firstname;
			$lastname = $originalData->nls_lastname;
			if (empty($firstname) && empty($lastname)) {
				$import->validationMessage = "No name supplied";
				continue;
			}
			if (empty($firstname)) {//if only last name supplied we move it to firstname
				$firstname = $lastname;
				$lastname = "";
			}
			$cleandata->nls_firstname = $firstname;
			$cleandata->nls_lastname = $lastname;

			//CHECK COUNTRY
			if (!empty($originalData->nls_country_name)) {
				$res = AZMailerLocationHelper::getCountryIdByName($originalData->nls_country_name, true);
				if (!$res) {
					$import->validationMessage = "Unable to register subscriber in country";
					continue;
				}
				$cleandata->nls_country_id = $res;
			} else {
				$cleandata->nls_country_id = $defaultValues["nls_country_id"];
			}

			//CHECK REGION
			if (!empty($originalData->nls_region_name)) {
				$res = AZMailerLocationHelper::getRegionIdByName($originalData->nls_region_name, $cleandata->nls_country_id, true);
				if (!$res) {
					$import->validationMessage = "Unable to register subscriber in region";
					continue;
				}
				$cleandata->nls_region_id = $res;
			} else {
				$cleandata->nls_region_id = $defaultValues["nls_region_id"];
			}

			//CHECK PROVINCE
			if (!empty($originalData->nls_province_name)) {
				$res = AZMailerLocationHelper::getProvinceIdByName($originalData->nls_province_name, $cleandata->nls_region_id, true);
				if (!$res) {
					$import->validationMessage = "Unable to register subscriber in province";
					continue;
				}
				$cleandata->nls_province_id = $res;
			} else {
				$cleandata->nls_province_id = $defaultValues["nls_province_id"];
			}

			//CHECK CATEGORY 1
			$res = AZMailerCategoryHelper::getCategoryIdArrayByNames(1, $originalData->nls_cat_1_lst, true);
			if (!count($res)) {
				$res = $defaultValues["nls_cat_1"];
			}
			$cleandata->nls_cat_1 = $res;

			//CHECK CATEGORY 2
			$res = AZMailerCategoryHelper::getCategoryIdArrayByNames(2, $originalData->nls_cat_2_lst, true);
			if (!count($res)) {
				$res = $defaultValues["nls_cat_2"];
			}
			$cleandata->nls_cat_2 = $res;

			//CHECK CATEGORY 3
			$res = AZMailerCategoryHelper::getCategoryIdArrayByNames(3, $originalData->nls_cat_3_lst, true);
			if (!count($res)) {
				$res = $defaultValues["nls_cat_3"];
			}
			$cleandata->nls_cat_3 = $res;

			//CHECK CATEGORY 4
			$res = AZMailerCategoryHelper::getCategoryIdArrayByNames(4, $originalData->nls_cat_4_lst, true);
			if (!count($res)) {
				$res = $defaultValues["nls_cat_4"];
			}
			$cleandata->nls_cat_4 = $res;

			//CHECK CATEGORY 5
			$res = AZMailerCategoryHelper::getCategoryIdArrayByNames(5, $originalData->nls_cat_5_lst, true);
			if (!count($res)) {
				$res = $defaultValues["nls_cat_5"];
			}
			$cleandata->nls_cat_5 = $res;

			//BLACKLIST
			$cleandata->nls_blacklisted = ($originalData->nls_blacklisted === false ? $defaultValues["nls_blacklisted"] : $originalData->nls_blacklisted);

			//if we got here then import is valid
			$import->valid = true;
			$import->validationMessage = "ok";
			//$import->originalData = $originalData;//for debugging only
			$import->data = self::checkAndBeautifyNLSData($cleandata);
			//$import->data = $cleandata;
		}
		return $imports;
	}

	/**
	 * @param string $mail
	 * @return bool
	 */
	public static function checkIfEmailSyntaxIsValid($mail = '') {
		$answer = true;
		$mailregex = '^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$';
		if (!preg_match("/$mailregex/", strtolower($mail))) {
			$answer = false;
		}
		return ($answer);
	}

	/**
	 * TODO: names like "d'Annunzio" will be convertted to "d'annunzio" which is not ok - split first on "'" and check
	 * @param array|\stdClass $data
	 * @return bool|array|\stdClass
	 */
	public static function checkAndBeautifyNLSData($data) {
		if (is_array($data)) {
			if (self::checkIfEmailSyntaxIsValid(trim($data["nls_email"]))) {
				$data["nls_email"] = strtolower(trim($data["nls_email"]));
				$data["nls_firstname"] = ucwords(strtolower(trim($data["nls_firstname"])));
				$data["nls_lastname"] = ucwords(strtolower(trim($data["nls_lastname"])));
			} else {
				$data = false;
			}
		} else if (is_object($data)) {
			if (self::checkIfEmailSyntaxIsValid(trim($data->nls_email))) {
				$data->nls_email = strtolower(trim($data->nls_email));
				$data->nls_firstname = ucwords(strtolower(trim($data->nls_firstname)));
				$data->nls_lastname = ucwords(strtolower(trim($data->nls_lastname)));
			} else {
				$data = false;
			}
		}
		return ($data);
	}

}
