<?php
/**
 * @package AZ Newsletter subsription module for Joomla! 1.5
 * @author Adam Jakab
 * @license GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 **/
defined( '_JEXEC' ) or die( 'Restricted access' );
require_once(JPATH_ADMINISTRATOR."/components/com_azmailer/includes/defines.php");
require_once(JPATH_ADMINISTRATOR."/components/com_azmailer/includes/loader.php");
use AZMailer\Helpers\AZMailerAdminInterfaceHelper;

//Create the AZMailer core class - NO INIT!
$AZMC = new \AZMailer\AZMailerCore();
$com_name = $AZMC->getOption('com_name');

//PARAMS
$layout                     = $params->get('layout', 'default');
$moduleclass_sfx            = $params->get('moduleclass_sfx' , '');
$load_jquery                = (int) $params->get('load_jquery', 0);
$load_fancybox              = (int) $params->get('load_fancybox', 0);
//
$module_pretext             = $params->get('module_pretext', '');
//
$request_country            = (int) $params->get('request_country', 0);
$request_region             = (int) $params->get('request_region', 0);
$request_province           = (int) $params->get('request_province', 0);
//
$request_privacy            = (int) $params->get('request_privacy', 0);
$privacy_article_url        = $params->get('privacy_article_url', "");
//
$popup_welcome_page         = (int) $params->get('popup_welcome_page', 0);
$welcome_page_url           = $params->get('welcome_page_url', "");
//



//LOAD jQuery
if ($load_jquery!=0) {
	AZMailerAdminInterfaceHelper::getJQueryLibrarySupport(($load_jquery==1));
}

//LOAD jQuery FancyBox
if ($load_fancybox!=0) {
	AZMailerAdminInterfaceHelper::addAdditionalHeaderIncludes("css", "/assets/js/fancybox/jquery.fancybox.css");
	AZMailerAdminInterfaceHelper::addAdditionalHeaderIncludes("js", "/assets/js/fancybox/jquery.fancybox.pack.js");
}

//RENDER MODULE
require(JModuleHelper::getLayoutPath('mod_azmailersubscribe', $layout));


