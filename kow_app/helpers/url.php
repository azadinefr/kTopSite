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

$theme_path = &kow_Framework::get_instance()->get('config', 'theme_path');
define('THEME_PATH', $theme_path);

function url($url, $name)
{
	echo '<a href="index.php?p=' . $url . '" alt="' . $name . '">' . $name . '</a>';
}

function redirect($url)
{
	header('Location: ' . $url);
}

function css($name)
{
	echo '<link href="' . THEMES_PATH . THEME_PATH . '/css/' . $name . '" rel="stylesheet">' . PHP_EOL;
}

function img($name, $alt)
{
	echo '<img src="' . THEMES_PATH . THEME_PATH . '/images/' .  $name . '" alt="' . $alt . '" />';
}

function js($name)
{
	echo '<script src="' . THEMES_PATH . THEME_PATH . '/jscripts/' . $name . '"></script>' . PHP_EOL;
}