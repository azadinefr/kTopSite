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

/**
 * Contrôleur et action utilisé par défaut
 * Par défaut : page/index
 */
$config['default_controller'] = 'page';
$config['default_action'] = 'index';

/**
 * Vue par défaut en cas d'erreur 404 (page demandée inexistante)
 * Par défaut : vue error_404 du contrôleur par défaut (page/error_404)
 */
$config['default_error404_view'] = 'error404';

/**
 * Nom du thème (= nom du dossier contenant le thème)
 * Par défaut : default
 */
$config['theme_path'] = 'default';

/**
 * Définit le décalage horaire par défaut de toutes les fonctions date/heure
 * Liste des fuseaux horaires : http://ch.php.net/manual/fr/timezones.php
 * Par défaut : Europe/Zurich (GMT + 1)
 */
$config['timezone'] = 'Europe/Zurich';

/**
 * Activer (true) ou désactiver (false) le système de hooks
 * Par défaut : true (activé)
 */
$config['enable_hooks'] = true;

/**
 * Chargement automatique des helpers spéficié ci-dessous
 * Par défaut : url
 */
$config['autoload_helpers'] = array('url', 'alert');

/**
 * Paramètre pour la connexion à une base de données
 */
/*
$config['database'] = array(
		'default' => array(
			'host' 		=> 'localhost',
			'port'		=> 3306,
			'database'	=> '',
			'username'	=> 'root',
			'password'	=> '',
			'options'	=> array(
				PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
				PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING
			)
		)
);
*/