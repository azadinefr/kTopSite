<?php

/**
 * Copyright (C) 2011-2012 Kevin Ryser <http://framework.koweb.ch>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program. If not, see <http://www.gnu.org/licenses/>.
 */

define('DEBUG_MODE', true);

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
    define('BASE_URL', dirname($_SERVER['SCRIPT_NAME']) . '/');

define('EXT', 				'.php');
define('SEP',				'/');

define('SYS_PATH', 			'kow_system/');
define('APP_PATH', 			'kow_app');

define('CONFIG_PATH', 		APP_PATH . SEP . 'inc/config' . EXT);

define('MODELS_PATH', 		APP_PATH .  SEP . 'models/');
define('VIEWS_PATH', 		APP_PATH .  SEP . 'views/');
define('CONTROLLERS_PATH', 	APP_PATH .  SEP . 'controllers/');

define('HELPERS_PATH', 		APP_PATH .  SEP . 'helpers/');
define('LIBS_PATH',			APP_PATH .  SEP . 'librairies/');
define('PLUGINS_PATH',		APP_PATH .  SEP . 'plugins/');
define('THEMES_PATH', 		APP_PATH .  SEP . 'themes/');

session_start();

require_once SYS_PATH . 'controller' . EXT;
require_once SYS_PATH . 'exception' . EXT;
require_once SYS_PATH . 'loader' . EXT;
require_once SYS_PATH . 'kowframework' . EXT;

kow_Framework::get_instance()->run();