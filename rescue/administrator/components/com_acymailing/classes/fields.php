<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	4.8.0
 * @author	acyba.com
 * @copyright	(C) 2009-2014 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php

class fieldsClass extends acymailingClass{

	var $tables = array('fields');
	var $pkey = 'fieldid';
	var $errors = array();
	var $prefix = 'field_';
	var $suffix = '';
	var $excludeValue = array();
	var $formoption = '';

	var $labelClass = '';

	var $dispatcher;

	function  __construct( $config = array() ){
		JPluginHelper::importPlugin('acymailing');
		$this->dispatcher = JDispatcher::getInstance();
		return parent::__construct($config);
	}

	function getFields($area,&$user){

		if(empty($user)) $user = new stdClass();

		$where = array();
		$where[] = 'a.`published` = 1';
		if($area == 'backend'){
			$where[] = 'a.`backend` = 1';
			$where[] = 'a.`core` = 0';
		}elseif($area == 'backlisting'){
			$where[] = 'a.`listing` = 1';
		}elseif($area == 'frontcomp'){
			$where[] = 'a.`frontcomp` = 1';
		}elseif($area == 'frontlisting'){
			$where[] = 'a.`frontlisting` = 1';
		}elseif($area == 'frontjoomlaprofile'){
			$where[] = 'a.`frontjoomlaprofile` = 1';
		}elseif($area == 'frontjoomlaregistration'){
			$where[] = 'a.`frontjoomlaregistration` = 1';
		}elseif($area == 'joomlaprofile'){
			$where[] = 'a.`joomlaprofile` = 1';
		}elseif($area == 'module'){
		}elseif($area != 'all'){
			$area = $this->database->Quote($area);
			$namesField = str_replace(",", $area[0].",".$area[0],$area);
			$where[] = "a.`namekey` IN (".$namesField.")";
		}

		$this->database->setQuery('SELECT * FROM `#__acymailing_fields` as a WHERE '.implode(' AND ',$where).' ORDER BY a.`ordering` ASC');
		$fields = $this->database->loadObjectList('namekey');

		foreach($fields as $namekey => $field){
			if(!empty($fields[$namekey]->options)){
				$fields[$namekey]->options = unserialize($fields[$namekey]->options);
			}
			if(!empty($field->value)){
				$fields[$namekey]->value = $this->explodeValues($fields[$namekey]->value);
			}
			if($field->type == 'file') $this->formoption = 'enctype="multipart/form-data"';
			if(empty($user->subid)) $user->$namekey = $field->default;
		}
		return $fields;
	}

	function getFieldName($field){
		$addLabels = array('textarea','text','dropdown','multipledropdown','file');
		return '<label '.(empty($this->labelClass) ? '' : ' class="'.$this->labelClass.'" ').(in_array($field->type,$addLabels) ? ' for="'.$this->prefix.$field->namekey.$this->suffix.'" ' : '' ).'>'.$this->trans($field->fieldname).'</label>';
	}

	function trans($name){
		if(preg_match('#^[A-Z_]*$#',$name)){
			return JText::_($name);
		}
		return $name;
	}

	function listing($field,$value,$search = ''){
		$functionType = '_listing'.ucfirst($field->type);

		if(method_exists($this,$functionType)) return $this->$functionType($field,$value);

		ob_start();
		$resultTrigger = $this->dispatcher->trigger('onAcyListingField_' . $field->type, array($field,$value));
		$pluginField = ob_get_clean();

		if(!empty($pluginField)) return $pluginField;
		else return acymailing_dispSearch(nl2br($this->trans($value)),$search);
	}

	function explodeValues($values){
		$allValues = explode("\n",$values);
		$returnedValues = array();
		foreach($allValues as $id => $oneVal){
			$line = explode('::',trim($oneVal));
			$var = @$line[0];
			$val = @$line[1];
			if(strlen($val)<1) continue;

			$obj = new stdClass();
			$obj->value = $val;
			for($i=2;$i<count($line);$i++){
				$obj->{$line[$i]} = 1;
			}
			$returnedValues[$var] = $obj;
		}
		return $returnedValues;
	}

}
