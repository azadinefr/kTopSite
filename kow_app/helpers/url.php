<?php

if(!defined('KOWFRAMEWORK')) exit('You can\'t access this ressource.');

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

$kfw_config = kow_Framework::get_instance()->get('config');
define('THEME_PATH', $kfw_config['theme_path']);
define('URL_REWRITING_ENABLED', $kfw_config['enable_url_rewriting']);

function url_path($url)
{
	if(URL_REWRITING_ENABLED)
		echo BASE_URL . $url;
	else
		echo 'index.php?p=' . $url;
}

function img_path($image)
{
	echo BASE_URL . THEMES_PATH . THEME_PATH . '/images/' .  $image;
}

function redirect($url)
{
	if(URL_REWRITING_ENABLED)
		header('Location: ' . BASE_URL . $url);
	else
		header('Location: index.php?p=' . $url);
}

function css($name)
{
	echo '<link href="' . BASE_URL . THEMES_PATH . THEME_PATH . '/css/' . $name . '" rel="stylesheet">' . PHP_EOL;
}

function js($name)
{
	echo '<script src="' . BASE_URL . THEMES_PATH . THEME_PATH . '/jscripts/' . $name . '"></script>' . PHP_EOL;
}