<?php
defined('_JEXEC') or die('Restricted access');
/**
 * @package AZMailer
 * @author Adam Jakab
 * @license GNU/GPL
 **/
require_once 'AZMailerTableInfo.php';

/**
 * Class tbl_azmailer_province
 */
class tbl_azmailer_province extends AZMailerTableInfo {
	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
		$this->name = 'azmailer_province';
		$this->pk = 'id';
		$this->columns = $this->getColumns();
		$this->keys = array("region_id" => false);
		$this->data = $this->getData();
		$this->forceNewDataInsert = false;
		$this->forceNewDataInsert_checkColumn = "";
	}

	/**
	 * @return array
	 */
	private function getColumns() {
		$answer = array(
			array("Field" => "id", "Type" => "int(11) unsigned", "Null" => "NO", "Default" => "", "Extra" => "auto_increment"),
			array("Field" => "region_id", "Type" => "int(11) unsigned", "Null" => "NO", "Default" => "", "Extra" => ""),
			array("Field" => "province_name", "Type" => "varchar(64)", "Null" => "NO", "Default" => "", "Extra" => ""),
			array("Field" => "province_sigla", "Type" => "varchar(8)", "Null" => "YES", "Default" => "", "Extra" => "")
		);
		return ($answer);
	}

	/**
	 * @return array
	 */
	private function getData() {
		$answer = array();
		return ($answer);
	}

	/*
	private function getDataOld() {
		$answer = array();

		//PROVINCES FOR: ITA - Abruzzo
		$RID = $this->___loadSqlSingleResult($this->getSqlForRegionId('IT', 'ABR'));
		$answer = array_merge($answer,
			$answer = array(
				array($RID, 'Chieti', null),
				array($RID, 'L\'Aquila', null),
				array($RID, 'Pescara', null),
				array($RID, 'Teramo', null)
			)
		);

		//PROVINCES FOR: ITA - Basilicata
		$RID = $this->___loadSqlSingleResult($this->getSqlForRegionId('IT', 'BAS'));
		$answer = array_merge($answer,
			$answer = array(
				array($RID, 'Matera', null),
				array($RID, 'Potenza', null)
			)
		);

		//PROVINCES FOR: ITA - Calabria
		$RID = $this->___loadSqlSingleResult($this->getSqlForRegionId('IT', 'CAL'));
		$answer = array_merge($answer,
			$answer = array(
				array($RID, 'Catanzaro', null),
				array($RID, 'Cosenza', null),
				array($RID, 'Crotone', null),
				array($RID, 'Reggio Calabria', null),
				array($RID, 'Vibo Valentia', null)
			)
		);

		//PROVINCES FOR: ITA - Campania
		$RID = $this->___loadSqlSingleResult($this->getSqlForRegionId('IT', 'CAM'));
		$answer = array_merge($answer,
			$answer = array(
				array($RID, 'Avellino', null),
				array($RID, 'Benevento', null),
				array($RID, 'Caserta', null),
				array($RID, 'Napoli', null),
				array($RID, 'Salerno', null)
			)
		);

		//PROVINCES FOR: ITA - Emilia-Romagna
		$RID = $this->___loadSqlSingleResult($this->getSqlForRegionId('IT', 'EMR'));
		$answer = array_merge($answer,
			$answer = array(
				array($RID, 'Bologna', null),
				array($RID, 'Ferrara', null),
				array($RID, 'Forl', null),
				array($RID, 'Modena', null),
				array($RID, 'Parma', null),
				array($RID, 'Piacenza', null),
				array($RID, 'Ravenna', null),
				array($RID, 'Reggio Emilia', null),
				array($RID, 'Rimini', null)
			)
		);

		//PROVINCES FOR: ITA - Friuli-Venezia Giulia
		$RID = $this->___loadSqlSingleResult($this->getSqlForRegionId('IT', 'FVG'));
		$answer = array_merge($answer,
			$answer = array(
				array($RID, 'Gorizia', null),
				array($RID, 'Pordenone', null),
				array($RID, 'Trieste', null),
				array($RID, 'Udine', null)
			)
		);

		//PROVINCES FOR: ITA - Lazio
		$RID = $this->___loadSqlSingleResult($this->getSqlForRegionId('IT', 'LAZ'));
		$answer = array_merge($answer,
			$answer = array(
				array($RID, 'Frosinone', null),
				array($RID, 'Latina', null),
				array($RID, 'Rieti', null),
				array($RID, 'Roma', null),
				array($RID, 'Viterbo', null)
			)
		);

		//PROVINCES FOR: ITA - Liguria
		$RID = $this->___loadSqlSingleResult($this->getSqlForRegionId('IT', 'LIG'));
		$answer = array_merge($answer,
			$answer = array(
				array($RID, 'Genova', null),
				array($RID, 'Imperia', null),
				array($RID, 'La Spezia', null),
				array($RID, 'Savona', null)
			)
		);

		//PROVINCES FOR: ITA - Lombardia
		$RID = $this->___loadSqlSingleResult($this->getSqlForRegionId('IT', 'LOM'));
		$answer = array_merge($answer,
			$answer = array(
				array($RID, 'Bergamo', null),
				array($RID, 'Brescia', null),
				array($RID, 'Como', null),
				array($RID, 'Cremona', null),
				array($RID, 'Lecco', null),
				array($RID, 'Lodi', null),
				array($RID, 'Mantova', null),
				array($RID, 'Milano', null),
				array($RID, 'Monza Brianza', null),
				array($RID, 'Pavia', null),
				array($RID, 'Sondrio', null),
				array($RID, 'Varese', null)
			)
		);

		//PROVINCES FOR: ITA - Marche
		$RID = $this->___loadSqlSingleResult($this->getSqlForRegionId('IT', 'MAR'));
		$answer = array_merge($answer,
			$answer = array(
				array($RID, 'Ancona', null),
				array($RID, 'Ascoli Piceno', null),
				array($RID, 'Fermo', null),
				array($RID, 'Macerata', null),
				array($RID, 'Pesaro e Urbino', null)
			)
		);

		//PROVINCES FOR: ITA - Molise
		$RID = $this->___loadSqlSingleResult($this->getSqlForRegionId('IT', 'MOL'));
		$answer = array_merge($answer,
			$answer = array(
				array($RID, 'Campobasso', null),
				array($RID, 'Isernia', null)
			)
		);

		//PROVINCES FOR: ITA - Piemonte
		$RID = $this->___loadSqlSingleResult($this->getSqlForRegionId('IT', 'PMT'));
		$answer = array_merge($answer,
			$answer = array(
				array($RID, 'Alessandria', null),
				array($RID, 'Asti', 'AT'),
				array($RID, 'Biella', null),
				array($RID, 'Cuneo', 'CN'),
				array($RID, 'Novara', 'NO'),
				array($RID, 'Torino', 'TO'),
				array($RID, 'Verbano-Cusio-Ossola', null),
				array($RID, 'Vercelli', null)
			)
		);

		//PROVINCES FOR: ITA - Puglia
		$RID = $this->___loadSqlSingleResult($this->getSqlForRegionId('IT', 'PUG'));
		$answer = array_merge($answer,
			$answer = array(
				array($RID, 'Bari', null),
				array($RID, 'Barletta-Andria-Trani', null),
				array($RID, 'Brindisi', null),
				array($RID, 'Foggia', null),
				array($RID, 'Lecce', null),
				array($RID, 'Taranto', null)
			)
		);

		//PROVINCES FOR: ITA - Sardegna
		$RID = $this->___loadSqlSingleResult($this->getSqlForRegionId('IT', 'SRD'));
		$answer = array_merge($answer,
			$answer = array(
				array($RID, 'Cagliari', null),
				array($RID, 'Carbona-Iglesias', null),
				array($RID, 'Medio Campidano', null),
				array($RID, 'Nuoro', null),
				array($RID, 'Ogliastra', null),
				array($RID, 'Olbia-Tempio', null),
				array($RID, 'Oristano', null),
				array($RID, 'Sassari', null)
			)
		);

		//PROVINCES FOR: ITA - Sicilia
		$RID = $this->___loadSqlSingleResult($this->getSqlForRegionId('IT', 'SIC'));
		$answer = array_merge($answer,
			$answer = array(
				array($RID, 'Agrigento', null),
				array($RID, 'Caltanisetta', null),
				array($RID, 'Catania', null),
				array($RID, 'Enna', null),
				array($RID, 'Messina', null),
				array($RID, 'Palermo', null),
				array($RID, 'Ragusa', null),
				array($RID, 'Siracusa', null),
				array($RID, 'Trapani', null)
			)
		);

		//PROVINCES FOR: ITA - Toscana
		$RID = $this->___loadSqlSingleResult($this->getSqlForRegionId('IT', 'TOS'));
		$answer = array_merge($answer,
			$answer = array(
				array($RID, 'Arezzo', null),
				array($RID, 'Firenze', null),
				array($RID, 'Grosseto', null),
				array($RID, 'Livorno', null),
				array($RID, 'Lucca', null),
				array($RID, 'Massa-Carrara', null),
				array($RID, 'Pisa', null),
				array($RID, 'Pistoia', null),
				array($RID, 'Prato', null),
				array($RID, 'Siena', null)
			)
		);

		//PROVINCES FOR: ITA - Trentino-Alto Adige
		$RID = $this->___loadSqlSingleResult($this->getSqlForRegionId('IT', 'TAA'));
		$answer = array_merge($answer,
			$answer = array(
				array($RID, 'Bolzano', null),
				array($RID, 'Trento', null)
			)
		);

		//PROVINCES FOR: ITA - Umbria
		$RID = $this->___loadSqlSingleResult($this->getSqlForRegionId('IT', 'UMB'));
		$answer = array_merge($answer,
			$answer = array(
				array($RID, 'Perugia', null),
				array($RID, 'Terni', null)
			)
		);

		//PROVINCES FOR: ITA - Valle d\'Aosta
		$RID = $this->___loadSqlSingleResult($this->getSqlForRegionId('IT', 'VLA'));
		$answer = array_merge($answer,
			$answer = array(
				array($RID, 'Aosta', null)
			)
		);

		//PROVINCES FOR: ITA - Veneto
		$RID = $this->___loadSqlSingleResult($this->getSqlForRegionId('IT', 'VEN'));
		$answer = array_merge($answer,
			$answer = array(
				array($RID, 'Belluno', null),
				array($RID, 'Padova', null),
				array($RID, 'Rovigo', null),
				array($RID, 'Treviso', null),
				array($RID, 'Venezia', null),
				array($RID, 'Verona', null),
				array($RID, 'Vicenza', null)
			)
		);

		return ($answer);
	}

	private function getSqlForRegionId($CSIG, $RSIG) {
		$sql = 'SELECT b.id FROM '.'#__'.'azmailer_country AS a'
			.' INNER JOIN '.'#__'.'azmailer_region AS b ON a.id = b.country_id'
			.' WHERE a.country_sigla = "'.$CSIG.'" AND b.region_sigla = "'.$RSIG.'"';
		return($sql);
	}
	*/

}
