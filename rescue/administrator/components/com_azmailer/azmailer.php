<?php
/**
 * @author     Adam Jakab {@link http://www.alfazeta.com}
 * @author     Created on 09-Feb-2013
 * @license    GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
//
require JPATH_COMPONENT_ADMINISTRATOR . '/includes/defines.php';
try {
	require JPATH_COMPONENT_ADMINISTRATOR . '/includes/loader.php';
} catch (Exception $e) {
	JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
	return;
}
//
$AZMC = new \AZMailer\AZMailerCore();
$AZMC->init("backend");
