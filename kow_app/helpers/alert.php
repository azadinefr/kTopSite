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

function set_flash($message, $type = 'error')
{
	if(!isset($_SESSION))
		throw new Exception('Vous devez activer les sessions (session_start();) sur la page d\'index.');

	$_SESSION['kow_flash'] = array(
		'message' 	=> $message,
		'type'		=> $type
	);
}

function flash()
{
	$r = null;
	if(isset($_SESSION['kow_flash']))
	{
		$r = $_SESSION['kow_flash'];
		unset($_SESSION['kow_flash']);
	}
	return $r;
}