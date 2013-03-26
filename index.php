<?php

/**
 * New BSD License
 *
 * Copyright (C) 2011-2013 Kevin Ryser (http://framework.koweb.ch) All rights reserved
 * See the LICENSE file for the full license text.
 */

/**
 * Activer (true) ou désactiver (false) le mode debug
 * Par défaut : activé (true)
 */
define('DEBUG_MODE', true);

define('EXT', 				'.php');
define('SEP',				'/');

define('SYS_PATH', 			'kow_system/');
define('APP_PATH', 			'kow_app');

define('CONFIG_PATH', 		APP_PATH . SEP . 'config/');
define('HELPERS_PATH', 		APP_PATH . SEP . 'helpers/');
define('LIBS_PATH',			APP_PATH . SEP . 'libraries/');
define('MODULES_PATH',		APP_PATH . SEP . 'modules/');
define('PLUGINS_PATH', 		APP_PATH . SEP . 'plugins/');
define('THEMES_PATH', 		APP_PATH . SEP . 'themes/');


/******************************************************
 * kowFramework. Ne pas toucher.
 ******************************************************/

if(DEBUG_MODE)
{
	error_reporting(E_ALL | E_STRICT);
	ini_set('display_errors', 'On');
}
else
	error_reporting(0);

if(($_SERVER['SCRIPT_NAME']) == '/')
    define('BASE_URL', '');
else
    define('BASE_URL', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/');

session_start();

require_once SYS_PATH . 'controller' . EXT;
require_once SYS_PATH . 'exception' . EXT;
require_once SYS_PATH . 'loader' . EXT;
require_once SYS_PATH . 'kowframework' . EXT;

kow_Framework::get_instance()->run();