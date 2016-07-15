<?php
/**
 * @author     Adam Jakab {@link http://www.alfazeta.com}
 * @author     Created on 09-Feb-2013
 * @license    GNU/GPL
 */
defined('_JEXEC') or die();

/**
 * From J!3 onwards there is no DS for directory separator
 * Forward slash should be ok for Win as well
 */
defined('DS') || define('DS', '/');

/**
 * A newline character for cleaner HTML styling.
 */
defined('BR') || define('BR', '<br />');

/**
 * A newline character for cleaner <pre> styling.
 */
defined('NL') || define('NL', "\n");

/**
 * Combined.
 */
defined('BRNL') || define('BRNL', BR . NL);

/**
 * Joomla versions
 */
\JLoader::import('cms.version.version');
$version = new \JVersion();
defined('IS_J3') || define('IS_J3', version_compare($version->RELEASE, '3.0', '>='));
unset($version);


