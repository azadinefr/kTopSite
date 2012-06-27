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

class kow_Exception
{
	public static function error_handler($errno, $errstr, $errfile, $errline)
	{
		if(!(error_reporting() & $errno))
			return;

		switch($errno)
		{
			case E_USER_ERROR:
			case E_ERROR:
				$type = 'Erreur : ';
				break;
			case E_USER_WARNING:
			case E_WARNING:
				$type = 'Alerte : ';
				break;
			case E_USER_NOTICE:
			case E_NOTICE:
				$type = 'Remarque : ';
				break;
			default :
				$type = 'Inconnu : ';
				break;
		}

		$trace = '';
		foreach(debug_backtrace() as $k => $v)
			if(!empty($v['line']) AND !empty($v['file']))
				$trace .= '<strong>Ligne : </strong>' . $v['line'] . ' <strong>du fichier : </strong> ' . $v['file'] . '<br />';

		echo '<html lang="fr"><head><meta charset="utf-8" /></head>
				<div style="background-color: #F6DDDD; border: 1px solid #FD1717; color: #8C2E0B; padding: 10px;">
					<h4>Une erreur PHP est survenue</h4>
					<strong>' . $type . '</strong>' . $errstr . '<br /><br />
					<strong>Ligne : </strong>' . $errline . ' <strong>du fichier : </strong> ' . $errfile . '<br /><br />
					<strong>Appel : </strong><br /><pre>'
				. $trace .
			 	'</pre></div>
			 </html>';

		exit;
	}

	public static function exception_handler($exception)
	{
		$trace = '';
		foreach($exception->getTrace() as $k => $v)
			if(!empty($v['line']) AND !empty($v['file']))
				$trace .= '<strong>Ligne : </strong>' . $v['line'] . ' <strong>du fichier : </strong> ' . $v['file'] . '<br />';

		echo '<html lang="fr"><head><meta charset="utf-8" /></head>
				<div style="background-color: #F6DDDD; border: 1px solid #FD1717; color: #8C2E0B; padding: 10px;">
					<h4>Une exception est survenue</h4>
					<strong>Message : </strong>' . $exception->getMessage() . '<br /><br />
					<strong>Ligne : </strong>' . $exception->getLine() . ' <strong>du fichier : </strong> ' . $exception->getFile() . '<br /><br />
					<strong>Appel : </strong><br /><pre>'
					. $trace .
			 	'</pre></div>
			 </html>';

		exit;
	}
}