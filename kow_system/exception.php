<?php

if(!defined('SYS_PATH')) exit('You can\'t access this ressource.');

/**
 * New BSD License
 *
 * Copyright (C) 2011-2013 Kevin Ryser (http://framework.koweb.ch) All rights reserved
 * See the LICENSE file for the full license text.
 */

class kow_Exception
{
	public static function error_handler($errno, $errstr, $errfile, $errline)
	{
		if(!(error_reporting() & $errno))
			return;

		if(!DEBUG_MODE)
		{
			echo '<html lang="fr"><head><meta charset="utf-8" /></head><body>Une erreur est survenue. Veuillez contacter l\'administrateur du site ou activer le mode debug.</body></html>';
			exit;
		}

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
			if(!empty($v['line']) and !empty($v['file']))
				$trace .= '<strong>Ligne : </strong>' . $v['line'] . ' <strong>du fichier : </strong> ' . $v['file'] . '<br />';

		echo '<html lang="fr"><head><meta charset="utf-8" /></head><body>
				<div style="background-color: #F6DDDD; border: 1px solid #FD1717; color: #8C2E0B; padding: 10px;">
					<h4>Une erreur PHP est survenue</h4>
					<strong>' . $type . '</strong>' . $errstr . '<br /><br />
					<strong>Ligne : </strong>' . $errline . ' <strong>du fichier : </strong> ' . $errfile . '<br /><br />
					<strong>Appel : </strong><br /><pre>'
				. $trace .
			 	'</pre></div>
			 </body></html>';

		exit;
	}

	public static function exception_handler($exception)
	{
		if(!DEBUG_MODE)
		{
			echo '<html lang="fr"><head><meta charset="utf-8" /></head><body>Une exception est survenue. Veuillez contacter l\'administrateur du site ou activer le mode debug.</body></html>';
			exit;
		}

		$trace = '';
		foreach($exception->getTrace() as $k => $v)
			if(!empty($v['line']) and !empty($v['file']))
				$trace .= '<strong>Ligne : </strong>' . $v['line'] . ' <strong>du fichier : </strong> ' . $v['file'] . '<br />';

		echo '<html lang="fr"><head><meta charset="utf-8" /></head><body>
				<div style="background-color: #F6DDDD; border: 1px solid #FD1717; color: #8C2E0B; padding: 10px;">
					<h4>Une exception est survenue</h4>
					<strong>Message : </strong>' . $exception->getMessage() . '<br /><br />
					<strong>Ligne : </strong>' . $exception->getLine() . ' <strong>du fichier : </strong> ' . $exception->getFile() . '<br /><br />
					<strong>Appel : </strong><br /><pre>'
					. $trace .
			 	'</pre></div>
			 </body></html>';

		exit;
	}
}