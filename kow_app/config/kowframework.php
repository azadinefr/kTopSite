<?php

if(!defined('KOWFRAMEWORK')) exit('You can\'t access this ressource.');

/**
 * New BSD License
 *
 * Copyright (C) 2011-2013 Kevin Ryser (http://framework.koweb.ch) All rights reserved
 * See the LICENSE file for the full license text.
 */

/**
 * Activer (true) ou désactiver (false) la ré-écriture d'URL
 * Si activé : http://localhost/kowframework/page/index
 * Attention, il faut éditer la ligne "RewriteBase" du fichier .htaccess
 * pour définir le répertoire courant du framework.
 * Si désactivé : http://localhost/kowframework/index.php?p=page/index
 * Par défaut : false (désactivé)
 */
$config['enable_url_rewriting'] = false;

/**
 * Module (contrôleur) par défaut
 * Par défaut : page
 */
$config['default_controller'] = 'topsite';

/**
 * Affiche le template "404" dans le template principal
 * Si false, affiche dans une nouvelle page
 * Par défaut : true
 */
$config['show_404_master'] = true;

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
 * Activer (true) ou désactiver (false) le système de plugin
 * Par défaut : false (désactivé)
 */
$config['enable_plugins'] = false;

/**
 * Liste des plugins à charger
 */
$config['plugins'] = array();

/**
 * Chargement automatique des helpers spéficié ci-dessous
 * Par défaut : url
 */
$config['autoload_helpers'] = array('url');

/**
 * Paramètre pour la connexion à une base de données
 */
$config['database'] = array(

		// Nom de la base de données
		'kowframework' => array(
			'host' 		=> 'localhost',
			'port'		=> 3306,
			'username'	=> 'root',
			'password'	=> '',
			'options'	=> array(
				PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
				PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING
			)
		)
);