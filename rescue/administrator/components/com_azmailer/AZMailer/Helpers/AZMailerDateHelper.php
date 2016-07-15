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
class AZMailerDateHelper {

	/**
	 * @param integer $ts
	 * @param string $format
	 * @return bool|string
	 */
	public static function convertToHumanReadableFormat($ts, $format = 'd/m/Y') {//if you want time use: 'd/m/Y G:i'
		return (date($format, $ts));
	}

	/**
	 * @param string $date
	 * @return integer
	 */
	public static function convertFromHumanReadableFormat($date) {
		//Convert Datetime from gg/mm/aaaa hh:mm:ss to timestamp
		//MUST DO SOME DATE CHECKING HERE - IF INVALID LET'S USE NOW()
		$array = explode(" ", $date);
		$array[0] = explode("/", $array[0]);
		if (isset($array[1])) {
			$array[1] = explode(":", $array[1]);
		} else {
			$array[1] = array(0, 0, 0);
		}
		for ($i = 0; $i < 3; $i++) {
			if (!isset($array[1][$i])) $array[1][$i] = 0;
		}
		$answer = mktime($array[1][0], $array[1][1], $array[1][2], $array[0][1], $array[0][0], $array[0][2]);
		return ($answer);
	}

	/**
	 * @param integer $ts
	 * @param boolean $is_abs - if true will return difference as a positive integer
	 * @return integer
	 */
	public static function getSecondsSince($ts = 0, $is_abs = true) {
		$diff = $ts - self::now();
		$diff = ($is_abs ? abs($diff) : $diff);
		return ($diff);
	}

	/**
	 * @return bool|string
	 */
	public static function now() {
		return (date("U"));
	}


}


/*
	//-----------------------------------------------------------------------DATE---------

	function getCurrentYear($format="Y") {
		return (date($format,$this->now()));
	}
	function getCurrentMonth($format="n") {
		return (date($format,$this->now()));
	}

	function getTodaysTS() {//return TS of the beginning of today (without hrs/min/sec offset)
		return($this->convertFromHumanReadableFormat($this->convertToHumanReadableFormat($this->now())));
	}



	function convertFromHumanReadableFormat($date) {
		//Convert Datetime from gg/mm/aaaa hh:mm:ss to timestamp
		//MUST DO SOME DATE CHECKING HERE - IF INVALID LET'S USE NOW()
		$array=explode(" ",$date);
		$array[0]=explode("/",$array[0]);
		if(isset($array[1])) { $array[1]=explode(":",$array[1]); }
			else { $array[1]=array(0,0,0); }
		for($i=0; $i<3; $i++) if(!isset($array[1][$i])) $array[1][$i]=0;
		//print_r($array);
		$answer=mktime($array[1][0], $array[1][1], $array[1][2], $array[0][1], $array[0][0], $array[0][2]);
		return ($answer);
	}

	function getDateDifferenceInDays($ts1,$ts2,$is_abs=true) {//is_abs=true will give difference always positive
		//if you don't set $ts2 we will use today's date
		if (empty($ts2)){$ts2=$this->now();}
		if ($is_abs) {
			$diff = abs($ts1 - $ts2);
		} else{
			$diff = $ts1 - $ts2;
		}
		return(floor($diff/60/60/24));
	}
 */
?>
