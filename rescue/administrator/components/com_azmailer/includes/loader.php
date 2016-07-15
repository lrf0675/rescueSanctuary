<?php
/**
 * @author     Adam Jakab {@link http://www.alfazeta.com}
 * @author     Created on 09-Feb-2013
 * @license    GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');

//Register Tables
\JTable::addIncludePath(dirname(__DIR__) . DS . 'tables');

/**
 * AZMailer Autoloader
 */
spl_autoload_register(function ($class) {
	if (preg_match('#^AZMailer\\\#', $class)) {
		if (!class_exists($class)) {
			$classpath = str_replace('\\', '/', $class) . '.php';
			//$fullpath = JPATH_ADMINISTRATOR.DS."components".DS."com_azmailer".DS.$classpath;
			$fullpath = dirname(__DIR__) . DS . $classpath;//so this will work even if in tmp folder during installation
			$realpath = realpath($fullpath);
			if ($realpath && file_exists($realpath)) {
				//echo "<br />Autoloading Class($class): $classpath - $realpath<br />";
				require_once($realpath);
			} else {
				//echo "<br />Autoloading Class Not Found($class): $fullpath !<br />";
			}
		}
	} else {
		//echo "<br />NOT AZMailer Class($class)!<br />";
	}
}, true, true);
