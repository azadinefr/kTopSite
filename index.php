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
	error_reporting(E_ALL);
	ini_set('display_errors', 'On');
}
else
	error_reporting(0);

if(($_SERVER['SCRIPT_NAME']) == '/')
    define('BASE_URL', '');
else
    define('BASE_URL', dirname($_SERVER['SCRIPT_NAME']) . '/');

define('SYS_PATH', 			'kow_system/');
define('APP_PATH', 			'kow_app/');

define('CONFIG_PATH', 		APP_PATH . 'inc/config.php');

define('MODELS_PATH', 		APP_PATH . 'models/');
define('VIEWS_PATH', 		APP_PATH . 'views/');
define('CONTROLLERS_PATH', 	APP_PATH . 'controllers/');

define('HELPERS_PATH', 		APP_PATH . 'helpers/');
define('PLUGINS_PATH',		APP_PATH . 'plugins/');
define('THEMES_PATH', 		APP_PATH . 'themes/');

define('EXT', 				'.php');

session_start();

require_once SYS_PATH . 'kowframework.php';
new kow_Framework();