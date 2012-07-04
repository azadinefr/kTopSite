<?php

if(!defined('SYS_PATH')) exit('You can\'t access this ressource.');

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

define('KOWFRAMEWORK', '1.0.0');

require_once SYS_PATH . 'exception' . EXT;

set_error_handler(array('kow_Exception', 'error_handler'));
set_exception_handler(array('kow_Exception', 'exception_handler'));
spl_autoload_register(array('kow_Framework', 'auto_load'));

class kow_Framework
{
	private static $_instance = null;
	private $_vars = array();

	public function __construct()
	{
		self::$_instance = &$this;

		if(!file_exists(CONFIG_PATH))
			throw new Exception('Le fichier de configuration "' . CONFIG_PATH . '" n\'existe pas.');

		require_once CONFIG_PATH;

		if(empty($config) OR !is_array($config))
			throw new Exception('Le fichier de configuration "' . CONFIG_PATH . '" est mal formaté.');

		if(@date_default_timezone_set(date_default_timezone_get()) === false)
			date_default_timezone_set($config['timezone']);

		$this->set('config', $config);
		
		$this->route();
		$c = $this->dispatch();
		$c->render();
	}

	public static function &get_instance()
	{
		return self::$_instance;
	}

	public static function auto_load($class)
	{
		$file = explode('_', strtolower($class));

		if(empty($file[0]) OR empty($file[1]))
			return;

		if($file[0] == 'kow')
			$file = SYS_PATH . $file[1] . EXT;
		else if(substr($file[0], -1) == 's')
			$file = APP_PATH . $file[0] . '/' . $file[1] . EXT;
		else
			$file = APP_PATH . $file[0] . 's/' . $file[1] . EXT;

		if(file_exists($file))
			require_once $file;
		else
			throw new Exception('Le fichier "' . $file . '" n\'existe pas. Tentative d\'inclusion pour la classe "' . $class . '"');
	}

	public function set($category, $key, $value = null)
	{
		if(is_null($value))
		{
			$this->_vars[$category] = array();
			$this->_vars[$category] = array_merge($this->_vars[$category], $key);
		}
		else
		{
			if(isset($this->_vars[$category]) && is_array($this->_vars[$category]))
				$this->_vars[$category][$key][] = $value;
			else
				$this->_vars[$category][$key] = $value;
		}
	}

	public function get($category, $key = null)
	{
		if(is_null($key))
			return $this->_vars[$category];
		else if(isset($this->_vars[$category][$key]))
			return $this->_vars[$category][$key];

		throw new Exception('Le paramètre "' . $key . '" de la catégorie "' . $category . '" n\'existe pas.');
	}

	public function route()
	{
		$controller = $this->get('config', 'default_controller');
		$action = $this->get('config', 'default_action');
		$params = array();

		if(!empty($_GET['p']))
		{
            $url = explode('/', $_GET['p']);

            if(!empty($url[0]))
            	if(file_exists(CONTROLLERS_PATH . 'Controller_' . $url[0]))
            		$controller = $url[0];

            if(!empty($url[1]))
            	$action = $url[1];

            $params = array_slice($url, 2);
		}

		$this->set('router', array(
			'controller'	=> $controller,
			'action'		=> $action,
			'params' 		=> $params,
			'routes'		=> array()
		));
	}

	public function dispatch()
	{
		$controller_class = 'Controller_' . ucfirst($this->get('router', 'controller'));
		$controller_path = CONTROLLERS_PATH . $this->get('router', 'controller') . EXT;
		$action = $this->get('router', 'action');
		$params = $this->get('router', 'params');
		
		if(!in_array($action, array_diff(
			get_class_methods($controller_class),
			get_class_methods(get_parent_class($controller_class)))
		))
			$action = $this->get('config', 'default_error404_view');
        
        $c = new $controller_class;

        foreach($this->get('config', 'autoload_helpers') as $v)
        		$c->load()->helper($v);

        call_user_func_array(array($c, $action), array($params));

        return $c;
	}
}