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

define('KOWFRAMEWORK', '1.0.4');

require_once SYS_PATH . 'exception' . EXT;

set_error_handler(array('kow_Exception', 'error_handler'));
set_exception_handler(array('kow_Exception', 'exception_handler'));
spl_autoload_register(array('kow_Framework', 'auto_load'));

class kow_Framework
{
	private static $_instance = null;
	private $_vars = array();
	private $_hook_list = array('pre_render', 'post_render');

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
		$this->load_plugins();
		
		$this->route();
		$c = $this->dispatch();
		$c->render();
	}

	public static function &get_instance()
	{
		return self::$_instance;
	}

	public function set($category, $key, $value = null, $force_array = false)
	{
		if(is_null($value))
		{
			$this->_vars[$category] = array();
			$this->_vars[$category] = array_merge($this->_vars[$category], $key);
		}
		else
		{
			if((isset($this->_vars[$category]) AND is_array($this->_vars[$category])) OR $force_array)
				$this->_vars[$category][$key][] = $value;
			else
				$this->_vars[$category][$key] = $value;
		}
	}

	public function get($category, $key = null, $show_exception = true)
	{
		if(is_null($key))
			return $this->_vars[$category];
		else if(isset($this->_vars[$category][$key]))
			return $this->_vars[$category][$key];

		if($show_exception)
			throw new Exception('Le paramètre "' . $key . '" de la catégorie "' . $category . '" n\'existe pas.');
		else
			return null;
	}

	// Todo check if class is already loaded
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

	public function load_plugins()
	{
		if($this->get('config', 'enable_plugins'))
		{
			foreach($this->get('config', 'plugins') as $file)
			{
				if(file_exists(PLUGINS_PATH . $file . EXT))
				{
					require_once PLUGINS_PATH . $file . EXT;
					$plugin_class = 'Plugin_' . ucfirst(end(explode('/', $file)));

					if(class_exists($plugin_class, false))
					{
						if(method_exists($plugin_class, 'load'))
							call_user_func(array($plugin_class, 'load'), $this->get('config'));

						foreach(get_class_methods($plugin_class) as $function)
							if(in_array($function, $this->_hook_list))
								$this->set('hooks', $function, array($plugin_class, $function), true);
					}
				}
				else
					throw new Exception('Le plugin "' . PLUGINS_PATH . $file . '" n\'existe pas.');
			}
		}
	}

	public static function register_hook($hook_name, $function)
	{
		kow_Framework::get_instance()->set('hooks', $hook_name, $function, true);
	}

	public static function run_hook($hook_name, &$arguments = array())
	{
		$hook_list = kow_Framework::get_instance()->get('hooks', $hook_name, false);

		if(!empty($hook_list))
		{
			$result = array();
			ksort($hook_list);

			foreach($hook_list as $function)
			{
				if(is_array($function))
				{
					if(method_exists($function[0], $function[1]))
						$result[$function[1]] = $function[0]::$function[1]($arguments);
				}
				else
				{
					if(function_exists($function))
						$result[$function] = $function($arguments);
				}
			}

			return $result;
		}
		return array();
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
            	if(file_exists(CONTROLLERS_PATH . $url[0] . EXT))
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

        kow_Framework::run_hook('pre_render');
        call_user_func_array(array($c, $action), array($params));

        return $c;
	}
}