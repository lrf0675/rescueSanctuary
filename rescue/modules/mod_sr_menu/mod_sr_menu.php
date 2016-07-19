<?php
/********************************************************************
Product		: Simple Responsive Menu
Date		: 27 February 2014
Copyright	: Les Arbres Design 2010-2014
Contact		: http://extensions.lesarbresdesign.info
Licence		: GNU General Public License
*********************************************************************/
defined('_JEXEC') or die;

// Include the syndicate functions only once
require_once __DIR__ . '/helper.php';

$list		= ModSRMenuHelper::getList($params);
$base		= ModSRMenuHelper::getBase($params);
$active		= ModSRMenuHelper::getActive($params);
$active_id 	= $active->id;
$path		= $base->tree;

$showAll	= $params->get('showAllChildren');
$class_sfx	= htmlspecialchars($params->get('class_sfx'));

$screen_width  = $params->get('screen_width');
$div_styles	   = htmlspecialchars($params->get('div_styles'));
$select_styles = htmlspecialchars($params->get('select_styles'));
$showAll2      = $params->get('showAllChildren2');
$fixedText     = $params->get('fixedText');

// Used for development:
// @file_put_contents("menu_log.txt", "\n\nPATH ARRAY\n\n".print_r($path,true)."\n");
// @file_put_contents("menu_log.txt", "\n\nBASE\n\n".print_r($base,true)." \n",FILE_APPEND);
// @file_put_contents("menu_log.txt", "\n\nACTIVE\n\n".print_r($active,true)."\n",FILE_APPEND);
// @file_put_contents("menu_log.txt", "\n\nLIST\n\n".print_r($list,true)."\n",FILE_APPEND);

if (count($list))
{

// draw the original menu
	
	require JModuleHelper::getLayoutPath('mod_sr_menu', $params->get('layout', 'default'));
	
// re-generate the item list if it's different for the responsive menu 

	if ($showAll != $showAll2)
		{
		$params->set('showAllChildren',$showAll2);
		$list = ModSRMenuHelper::getList($params);
		}

// draw the select list menu

	require JModuleHelper::getLayoutPath('mod_sr_menu', 'select_list');
	
// write the css that controls which menu is visible

	$styles  = "\n".'   div.srm_position {display:none;}';
	$styles .= "\n".'   ul.srm_ulmenu {display:block;}';
	$styles .= "\n".'   @media screen and (max-width:'.$screen_width.'px)';
	$styles .= "\n".'     {div.srm_position {display:block;}';
	$styles .= "\n".'      ul.srm_ulmenu {display:none;} }';
	$style   = "\n".'<style type="text/css">'.$styles."\n  </style>\n";
	$document = JFactory::getDocument();
	$document->addCustomTag($style);
}

