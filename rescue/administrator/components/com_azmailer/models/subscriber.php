<?php
/**
 * @package    AZMailer
 * @author     Adam Jakab {@link http://www.alfazeta.com}
 * @author     Created on 09-Feb-2013
 * @license    GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
use AZMailer\Core\AZMailerModel;
use AZMailer\Helpers\AZMailerDateHelper;
use AZMailer\Helpers\AZMailerSubscriberHelper;

//use AZMailer\Helpers\AZMailerTemplateHelper;

/**
 * Newsletter Subscriber Model
 */
class AZMailerModelSubscriber extends AZMailerModel {
	/**
	 * @param array $config
	 */
	public function __construct($config = array()) {
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'a.id', 'nls_firstname', 'nls_lastname', 'nls_email'
			);
		}
		parent::__construct($config);
	}

	/**
	 * TODO: this should return entity and NOT simple object - for now it is done in the view where needed
	 * @param null $id
	 * @return bool|object
	 */
	public function getSpecificItem($id = null) {
		if(! ($item = $this->_getSpecificItem($id)) ) {
			$item = $this->getTable();
		}
		return ($item);
	}

	/**
	 * TODO: we need our component specific exception and a general catcher so we don't end up un J!'s error page ;)
	 *
	 * @param array $data The posted data in array format
	 * @return bool
	 * @throws \AZMailer\Core\AZMailerException
	 */
	public function saveSpecificItem(array $data) {
		if (!AZMailerSubscriberHelper::checkIfNLSMailIsAvailable($data["nls_email"], $data["id"])) {
			throw new \AZMailer\Core\AZMailerException(\JText::_("COM_AZMAILER_SUBSCR_ERR_MAIL_REGISTERED"), 500);//already registered to another user error
		}
		//check lexical email validity and beautify data
		if (!($data = AZMailerSubscriberHelper::checkAndBeautifyNLSData($data))) {
			throw new \AZMailer\Core\AZMailerException(\JText::_("COM_AZMAILER_SUBSCR_ERR_MAIL"), 500);//invalid e-mail error
		}

		//cleanup/beautify data
		$data["nls_blacklisted"] = ($data["nls_blacklisted"] == "Y" ? 1 : 0);
		//CATEGORIES ARE SENT AS ARRAYS
		$data["nls_cat_1"] = json_encode(isset($data["nls_cat_1"]) ? $data["nls_cat_1"] : array());
		$data["nls_cat_2"] = json_encode(isset($data["nls_cat_2"]) ? $data["nls_cat_2"] : array());
		$data["nls_cat_3"] = json_encode(isset($data["nls_cat_3"]) ? $data["nls_cat_3"] : array());
		$data["nls_cat_4"] = json_encode(isset($data["nls_cat_4"]) ? $data["nls_cat_4"] : array());
		$data["nls_cat_5"] = json_encode(isset($data["nls_cat_5"]) ? $data["nls_cat_5"] : array());
		return($this->_saveSpecificItem($data));
	}

	/**
	 * @param $cidArray
	 * @return bool
	 */
	public function removeSpecificItems($cidArray) {
		$delcnt = 0;
		$table = $this->getTable();
		while (count($cidArray)) {
			$cid = array_pop($cidArray);
			$table->load($cid);
			if ($table->delete($cid)) {
				$delcnt++;
			}
		}
		return true;
	}

	/**
	 * @param null  $type
	 * @param null  $prefix
	 * @param array $config
	 * @return JTable|mixed
	 */
	public function getTable($type = null, $prefix = null, $config = array()) {
		return \JTable::getInstance(($type ? $type : 'azmailer_subscriber'), ($prefix ? $prefix : 'Table'), $config);
	}

	/**
	 * @return mixed
	 */
	public function getTotalRecords() {
		$db = \JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select("COUNT(*)");
		$query->from($db->quoteName('#__azmailer_subscriber'));
		$db->setQuery($query);
		return ($db->loadResult());
	}

	/**
	 * @param null $id
	 * @return mixed
	 */
	public function getCheckLogs($id = null) {
		$db = \JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select("nls_mail_validation_log");
		$query->from($db->quoteName('#__azmailer_subscriber'));
		$query->where("id = " . $id);
		$db->setQuery($query);
		return ($db->loadResult());
	}

	/**
	 * Elaborate import xls file and import subscribers to database
	 * @param $data
	 * @return mixed
	 */
	public function importSubscribersFromUploadedFile($data) {
		jimport('joomla.filesystem.file');
		$data->imports = array();
		//
		if (isset($data->post["uploadedfile"]) && is_array($data->post["uploadedfile"]) && $data->post["uploadedfile"]["error"] == 0) {
			$data->TMPFOLDER = JPATH_SITE . DS . 'tmp';
			$data->EXTENSION = strtolower(\JFile::getExt($data->post["uploadedfile"]["name"]));
			if (in_array($data->EXTENSION, array("xls"))) {//array("xls","xlsx","ods","csv")
				$data->FILENAME = md5('subscribers_' . rand(10, 100000000) . '_' . date('U')) . '.' . $data->EXTENSION;
				$tmpfilepath = $data->TMPFOLDER . DS . $data->FILENAME;
				if (\JFile::upload($data->post["uploadedfile"]["tmp_name"], $tmpfilepath)) {
					try {
						$insertedSubscribers = 0;
						//ok - file is in tmp folder so we can now initiate phpExcel
						set_include_path(JPATH_COMPONENT_ADMINISTRATOR . DS . "vendors" . DS . "phpExcel");
						include 'PHPExcel/IOFactory.php';
						/** @var PHPExcel_Reader_Abstract $objReader */
						$objReader = PHPExcel_IOFactory::createReaderForFile($tmpfilepath);
						$objReader->setReadDataOnly(true);
						/** @var PHPExcel $objPHPExcel */
						$objPHPExcel = $objReader->load($tmpfilepath);

						//get Sheet #1
						$objWorksheet = $objPHPExcel->getSheet(0);
						$numberOfRows = $objWorksheet->getHighestRow();

						//work out column indexes for import columns
						$numberOfColumns = count(AZMailerSubscriberHelper::getImportColumns());
						$sheetColumns = array();
						for ($i = 0; $i < $numberOfColumns; $i++) {
							$sheetColumns[] = $objWorksheet->getCellByColumnAndRow($i, 1)->getValue();
						}
						$importColumns = AZMailerSubscriberHelper::getImportColumnsWithIndexes($sheetColumns);
						$data->importColumns = $importColumns;


						//loop through data rows and get raw data from spreadsheet
						for ($rowIndex = 2; $rowIndex <= $numberOfRows; $rowIndex++) {
							//contact Data
							$NLCNT = new stdClass();
							$NLCNT->nls_email = AZMailerSubscriberHelper::getImportCellValue($objWorksheet, $importColumns, $rowIndex, "E-mail");
							$NLCNT->nls_firstname = AZMailerSubscriberHelper::getImportCellValue($objWorksheet, $importColumns, $rowIndex, "Firstname");
							$NLCNT->nls_lastname = AZMailerSubscriberHelper::getImportCellValue($objWorksheet, $importColumns, $rowIndex, "Lastname");
							$NLCNT->nls_country_name = AZMailerSubscriberHelper::getImportCellValue($objWorksheet, $importColumns, $rowIndex, "Country");
							$NLCNT->nls_region_name = AZMailerSubscriberHelper::getImportCellValue($objWorksheet, $importColumns, $rowIndex, "Region");
							$NLCNT->nls_province_name = AZMailerSubscriberHelper::getImportCellValue($objWorksheet, $importColumns, $rowIndex, "Province");
							$NLCNT->nls_cat_1_lst = AZMailerSubscriberHelper::getImportCellValue($objWorksheet, $importColumns, $rowIndex, "Category 1");
							$NLCNT->nls_cat_2_lst = AZMailerSubscriberHelper::getImportCellValue($objWorksheet, $importColumns, $rowIndex, "Category 2");
							$NLCNT->nls_cat_3_lst = AZMailerSubscriberHelper::getImportCellValue($objWorksheet, $importColumns, $rowIndex, "Category 3");
							$NLCNT->nls_cat_4_lst = AZMailerSubscriberHelper::getImportCellValue($objWorksheet, $importColumns, $rowIndex, "Category 4");
							$NLCNT->nls_cat_5_lst = AZMailerSubscriberHelper::getImportCellValue($objWorksheet, $importColumns, $rowIndex, "Category 5");
							$NLCNT->nls_blacklisted = AZMailerSubscriberHelper::getImportCellValue($objWorksheet, $importColumns, $rowIndex, "Blacklist");
							//
							$import = new \stdClass();
							$import->data = $NLCNT;
							$import->rowIndex = $rowIndex;
							array_push($data->imports, $import);
						}

						//We don't need phpEscel anymore so let's free up some memory
						unset($objWorksheet);
						unset($objPHPExcel);
						unset($objReader);

						//validate imports and merge with default values
						$data->imports = AZMailerSubscriberHelper::checkAndCleanUpXlsImportedData($data->imports, $data->post["defaults"]);

						//register subscribers - AZMailerSubscriber
						$validImports = 0;
						$importedSubscribers = 0;
						$OPTION_NLS_OVERWRITE = $data->post["defaults"]["nls_overwrite_existing"];//1=NO /2=overwrite ALL /3=MERGE(categories only)
						foreach ($data->imports as $import) {
							if ($import->valid) {
								$validImports++;
								$iData = $import->data;
								$NLS = AZMailerSubscriberHelper::getNewsletterSubscriberByMail($iData->nls_email);
								if (!$NLS || ($NLS && $OPTION_NLS_OVERWRITE > 1)) {
									if (!$NLS) {
										$NLS = new \stdClass();
									}
									//$import->nlsData = $NLS;//for debugging only
									$iData->id = (isset($NLS->id) ? $NLS->id : null);
									//$iData->nls_email - already set
									//$iData->nls_firstname - already set
									//$iData->nls_lastname - already set
									$iData->nls_subscribe_date = (isset($NLS->nls_subscribe_date) ? $NLS->nls_subscribe_date : AZMailerDateHelper::now());
									$iData->nls_ip = (isset($NLS->nls_ip) ? $NLS->nls_ip : '0.0.0.0');
									//$iData->nls_country_id - already set
									//$iData->nls_region_id - already set
									//$iData->nls_province_id - already set
									$iData->nls_cat_1 = json_encode($OPTION_NLS_OVERWRITE == 2 ?
											$iData->nls_cat_1 :
											array_unique(array_merge((isset($NLS->nls_cat_1) ? json_decode($NLS->nls_cat_1) : array()), $iData->nls_cat_1))
									);
									$iData->nls_cat_2 = json_encode($OPTION_NLS_OVERWRITE == 2 ?
											$iData->nls_cat_2 :
											array_unique(array_merge((isset($NLS->nls_cat_2) ? json_decode($NLS->nls_cat_2) : array()), $iData->nls_cat_2))
									);
									$iData->nls_cat_3 = json_encode($OPTION_NLS_OVERWRITE == 2 ?
											$iData->nls_cat_3 :
											array_unique(array_merge((isset($NLS->nls_cat_3) ? json_decode($NLS->nls_cat_3) : array()), $iData->nls_cat_3))
									);
									$iData->nls_cat_4 = json_encode($OPTION_NLS_OVERWRITE == 2 ?
											$iData->nls_cat_4 :
											array_unique(array_merge((isset($NLS->nls_cat_4) ? json_decode($NLS->nls_cat_4) : array()), $iData->nls_cat_4))
									);
									$iData->nls_cat_5 = json_encode($OPTION_NLS_OVERWRITE == 2 ?
											$iData->nls_cat_5 :
											array_unique(array_merge((isset($NLS->nls_cat_5) ? json_decode($NLS->nls_cat_5) : array()), $iData->nls_cat_5))
									);

									//BLACKLIST (Y/N) -> (1/0)
									$iData->nls_blacklisted = ($iData->nls_blacklisted == "Y" ? 1 : 0);

									//todo: entity please
									$table = \JTable::getInstance('azmailer_subscriber', 'Table');
									if ($table->bind($iData)) {
										if ($table->check()) {
											if ($table->store()) {
												$importedSubscribers++;
											}
										}
									}
								} else {
									//found subscriber by mail but user does NOT want to overwrite
								}
							}
						}

						$data->result = array(
							"lines in file" => ($numberOfRows - 1),
							"elaborated raw lines" => count($data->imports),
							"valid imports" => $validImports,
							"imported subscribers" => $importedSubscribers
						);
						\JFile::delete($tmpfilepath);

					} catch (\PHPExcel_Reader_Exception $e) {
						$data->errors[] = JText::sprintf("COM_AZMAILER_SUBSCR_UPLOAD_ERR_PHPEXCEL", $e->getMessage());
					}
				} else {
					$data->errors[] = \JText::_('COM_AZMAILER_SUBSCR_UPLOAD_ERR_MOVE');//"unable to move file.";
				}
			} else {
				$data->errors[] = \JText::_('COM_AZMAILER_SUBSCR_UPLOAD_ERR_NOXLS');//"Not an Excel file.";
			}
		} else {
			$data->errors[] = \JText::_('COM_AZMAILER_SUBSCR_UPLOAD_ERR_UPLOAD');//"Impossibile caricare il file.";
		}
		return ($data);
	}


	/*
	 * POSTED FORM DATA:
		 * {"errors":[],
		 * "post":{
		 *      "uploadedfile":{"name":"1377629513_sync.png","type":"image\/png","tmp_name":"\/tmp\/phplP04dA","error":0,"size":298},
		 *      "nls_overwrite_existing":2,
		 *      "nls_newoptions_geopos":1,
		 *      "nls_newoptions_category":0,
		 *      "nls_blacklisted":"N",
		 *      "nls_country_id":12,
		 *      "nls_region_id":0,
		 *      "nls_province_id":0,
		 *      "nls_cat_1":["1"],
		 *      "nls_cat_2":["16"],
		 *      "nls_cat_3":["18"],
		 *      "nls_cat_4":[],
		 *      "nls_cat_5":[]
		 * },
		 * "result":"ok"}
		 * */

	/**
	 * @return JDatabaseQuery
	 */
	protected function getListQuery() {
		$db = \JFactory::getDBO();
		$query = $db->getQuery(true);
		//
		// Select the required fields from the table.
		$query->select(
			$this->getState('list.select',
				'a.*'
			)
		);
		$query->from($db->quoteName('#__azmailer_subscriber') . ' AS a');

		//Search
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			$search = $db->quote('%' . $db->escape($search, true) . '%');
			$query->where('(a.nls_firstname LIKE ' . $search . ' OR a.nls_lastname LIKE ' . $search . ' OR a.nls_email LIKE ' . $search . ')');
		}

		//Location Filters

		if (($filterCountry = $this->getState('filter.country_sel'))) {
			$query->where('a.nls_country_id = ' . $filterCountry);
			if (($filterRegion = $this->getState('filter.region_sel'))) {
				$query->where('a.nls_region_id = ' . $filterRegion);
				if (($filterProvince = $this->getState('filter.province_sel'))) {
					$query->where('a.nls_province_id = ' . $filterProvince);
				}
			}
		}


		//Category Filters
		for ($CN = 1; $CN <= 5; $CN++) {
			$filterCat = $this->getState('filter.cat_sel_' . $CN);
			if ($filterCat != 0) {
				$query->where('a.nls_cat_' . $CN . ' REGEXP "\"' . $filterCat . '\""');
			}
		}

		//ORDERING
		$orderCol = $this->state->get('list.ordering', 'id');
		$orderDirn = $this->state->get('list.direction', 'ASC');
		$query->order($db->escape($orderCol . ' ' . $orderDirn));
		//
		return $query;
	}

	/**
	 * @param string $ordering
	 * @param string $direction
	 */
	protected function populateState($ordering = "id", $direction = "ASC") {
		//Filters
		$this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', "STRING"));

		//LOCATION SELECTIONS
		$this->setState('filter.country_sel', $this->getUserStateFromRequest($this->context . '.filter.country_sel', 'filter_country_sel', 0, "INT"));
		$this->setState('filter.region_sel', $this->getUserStateFromRequest($this->context . '.filter.region_sel', 'filter_region_sel', 0, "INT"));
		$this->setState('filter.province_sel', $this->getUserStateFromRequest($this->context . '.filter.province_sel', 'filter_province_sel', 0, "INT"));

		//CATEGORY SELECTIONS
		$this->setState('filter.cat_sel_1', $this->getUserStateFromRequest($this->context . '.filter.cat_sel_1', 'filter_cat_sel_1', 0, "INT"));
		$this->setState('filter.cat_sel_2', $this->getUserStateFromRequest($this->context . '.filter.cat_sel_2', 'filter_cat_sel_2', 0, "INT"));
		$this->setState('filter.cat_sel_3', $this->getUserStateFromRequest($this->context . '.filter.cat_sel_3', 'filter_cat_sel_3', 0, "INT"));
		$this->setState('filter.cat_sel_4', $this->getUserStateFromRequest($this->context . '.filter.cat_sel_4', 'filter_cat_sel_4', 0, "INT"));
		$this->setState('filter.cat_sel_5', $this->getUserStateFromRequest($this->context . '.filter.cat_sel_5', 'filter_cat_sel_5', 0, "INT"));

		//Component parameters
		$params = \JComponentHelper::getParams('com_azmailer');
		$this->setState('params', $params);
		//
		parent::populateState($ordering, $direction);
	}
}