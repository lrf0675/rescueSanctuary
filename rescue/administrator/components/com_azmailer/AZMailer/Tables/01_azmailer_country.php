<?php
defined('_JEXEC') or die('Restricted access');
/**
 * @package AZMailer
 * @author Adam Jakab
 * @license GNU/GPL
 **/
require_once 'AZMailerTableInfo.php';

/**
 * Class tbl_azmailer_country
 */
class tbl_azmailer_country extends AZMailerTableInfo {
	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
		$this->name = 'azmailer_country';
		$this->pk = 'id';
		$this->columns = $this->getColumns();
		$this->keys = array("country_sigla" => true);
		$this->data = $this->getData();
		$this->forceNewDataInsert = false;
		$this->forceNewDataInsert_checkColumn = "country_sigla";

	}

	/**
	 * @return array
	 */
	private function getColumns() {
		$answer = array(
			array("Field" => "id", "Type" => "int(11) unsigned", "Null" => "NO", "Default" => "", "Extra" => "auto_increment"),
			array("Field" => "country_name", "Type" => "varchar(64)", "Null" => "NO", "Default" => "", "Extra" => ""),
			array("Field" => "country_sigla", "Type" => "varchar(2)", "Null" => "NO", "Default" => "", "Extra" => "")
		);
		return ($answer);
	}

	/**
	 * @return array
	 */
	private function getData() {
		$answer = array(
			array('- Secret Location -', 'XX')
		);
		return ($answer);
	}

	/*
	private function getDataOld() {
		$answer = array(
			array('- Secret Location -','XX'),
			array('Afghanistan','AF'),
			array('Albania','AL'),
			array('Algeria','DZ'),
			array('Andorra','AD'),
			array('Angola','AO'),
			array('Argentina','AR'),
			array('Armenia','AM'),
			array('Australia','AU'),
			array('Austria','AT'),
			array('Azerbaijan','AZ'),
			array('Bahamas','BS'),
			array('Baharin','BH'),
			array('Bangladesh','BD'),
			array('Barbados','BB'),
			array('Belarus','BY'),
			array('Belgium','BE'),
			array('Belize','BZ'),
			array('Bhutan','BT'),
			array('Bolivia','BV'),
			array('Bosnia Herzegovina','BA'),
			array('Botswana','BW'),
			array('Brazil','BR'),
			array('Brunei','BN'),
			array('Bulgaria','BG'),
			array('Burkina Faso','BF'),
			array('Burundi','BI'),
			array('Cambodia','KH'),
			array('Cameroon','CM'),
			array('Canada','CA'),
			array('Cape Verde','CV'),
			array('Central African Republic','CF'),
			array('Chad','TD'),
			array('Chile','CL'),
			array('China','CN'),
			array('Colombia','CO'),
			array('Comoros','KM'),
			array('Congo','CG'),
			array('Costa Rica','CR'),
			array('Croatia','HR'),
			array('Cuba','CU'),
			array('Cyprus','CY'),
			array('Czech Republic','CZ'),
			array('Denmark','DK'),
			array('Djibouti','DJ'),
			array('Dominica','DM'),
			array('Dominican Republic','DO'),
			array('East Timor','TP'),
			array('Ecuador','EC'),
			array('Egypt','EG'),
			array('El Salvador','SV'),
			array('Equatorial Guinea','GQ'),
			array('Eritrea','ER'),
			array('Estonia','EE'),
			array('Ethiopia','ET'),
			array('Fiji','FJ'),
			array('Finland','FI'),
			array('France','FR'),
			array('Gabon','GA'),
			array('Gambia','GM'),
			array('Georgia','GE'),
			array('Germany','DE'),
			array('Ghana','GH'),
			array('Gibraltar','GI'),
			array('Greece','GR'),
			array('Greenland','GL'),
			array('Grenada','GD'),
			array('Guatemala','GT'),
			array('Guinea','GN'),
			array('Guyana','GY'),
			array('Haiti','HT'),
			array('Honduras','HN'),
			array('Hungary','HU'),
			array('Iceland','IS'),
			array('India','IN'),
			array('Indonesia','ID'),
			array('Iran','IR'),
			array('Iraq','IQ'),
			array('Ireland','IE'),
			array('Israel','IL'),
			array('Italy','IT'),
			array('Ivory Coast','CI'),
			array('Jamaica','JM'),
			array('Japan','JP'),
			array('Jordan','JO'),
			array('Kazakhstan','KZ'),
			array('Kenya','KE'),
			array('Kiribati','KI'),
			array('Korea North','KP'),
			array('Korea South','KR'),
			array('Kosovo','KV'),
			array('Kuwait','KW'),
			array('Kyrgyzstan','KG'),
			array('Laos','LA'),
			array('Latvia','LV'),
			array('Lebanon','LB'),
			array('Lesotho','LS'),
			array('Liberia','LR'),
			array('Libya','LY'),
			array('Liechtenstein','LI'),
			array('Lithuania','LT'),
			array('Luxembourg','LU'),
			array('Macedonia','MK'),
			array('Madagascar','MG'),
			array('Malawi','MW'),
			array('Malaysia','MY'),
			array('Maldives','MV'),
			array('Mali','ML'),
			array('Malta','MT'),
			array('Marshall Islands','MH'),
			array('Mauritania','MR'),
			array('Mauritius','MU'),
			array('Mexico','MX'),
			array('Micronesia','FM'),
			array('Moldova','MD'),
			array('Monaco','MC'),
			array('Mongolia','MN'),
			array('Montenegro','CS'),
			array('Morocco','MA'),
			array('Mozambique','MZ'),
			array('Myanmar','MM'),
			array('Namibia','NA'),
			array('Nauru','NR'),
			array('Nepal','NP'),
			array('Netherlands','NL'),
			array('New Zealand','NZ'),
			array('Nicaragua','NI'),
			array('Niger','NE'),
			array('Nigeria','NG'),
			array('Norway','NO'),
			array('Oman','OM'),
			array('Pakistan','PK'),
			array('Palau','PW'),
			array('Panama','PA'),
			array('Papua New Guinea','PG'),
			array('Paraguay','PY'),
			array('Peru','PE'),
			array('Philippines','PH'),
			array('Poland','PL'),
			array('Portugal','PT'),
			array('Puerto Rico','PR'),
			array('Qatar','QA'),
			array('Romania','RO'),
			array('Russia','RU'),
			array('Rwanda','RW'),
			array('Samoa','WS'),
			array('Saudi Arabia','SA'),
			array('Senegal','SN'),
			array('Serbia','RS'),
			array('Sierra Leone','SL'),
			array('Slovakia','SK'),
			array('Slovenia','SI'),
			array('Solomon Islands','SB'),
			array('Somalia','SO'),
			array('South Africa','ZA'),
			array('Spain','ES'),
			array('Sri Lanka','LK'),
			array('Sudan','SD'),
			array('Suriname','SR'),
			array('Swaziland','SZ'),
			array('Sweeden','SE'),
			array('Switzerland','CH'),
			array('Syria','SY'),
			array('Taiwan','TW'),
			array('Tajikistan','TJ'),
			array('Tanzania','TZ'),
			array('Thailand','TH'),
			array('Togo','TG'),
			array('Tonga','TO'),
			array('Tunisia','TN'),
			array('Turkey','TR'),
			array('Turkmenistan','TM'),
			array('Uganda','UG'),
			array('Ukraine','UA'),
			array('United Arab Emirates','AE'),
			array('United Kingdom','UK'),
			array('United States','US'),
			array('Uruguay','UY'),
			array('Uzbekistan','UZ'),
			array('Venezuela','VE'),
			array('Vietnam','VN'),
			array('Yemen','YE'),
			array('Zambia','ZM'),
			array('Zimbabwe','ZW')
		);
		return ($answer);
	}*/
}
