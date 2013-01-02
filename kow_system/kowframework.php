<?php

if(!defined('SYS_PATH')) exit('You can\'t access this ressource.');

/**
 * New BSD License
 *
 * Copyright (C) 2011-2013 Kevin Ryser (http://framework.koweb.ch) All rights reserved
 * See the LICENSE file for the full license text.
 */

define('KOWFRAMEWORK', '1.0.15');

set_error_handler(array('kow_Exception', 'error_handler'));
set_exception_handler(array('kow_Exception', 'exception_handler'));

function kfw_version()
{
	echo KOWFRAMEWORK;
}

class kow_Framework
{
	private static $_instance = null;
	private $_vars = array();
	private $_hook_list = array('post_route', 'pre_render', 'post_render');

	private function __construct()
	{
		self::$_instance =& $this;

		if(!file_exists(CONFIG_PATH))
			throw new Exception('Le fichier de configuration "' . CONFIG_PATH . '" n\'existe pas.');

		require_once CONFIG_PATH;

		if(empty($config) or !is_array($config))
			throw new Exception('Le fichier de configuration "' . CONFIG_PATH . '" est mal formaté.');

		if(@date_default_timezone_set(date_default_timezone_get()) === false)
			date_default_timezone_set($config['timezone']);

		$this->set('config', $config);
		$this->load_plugins();
	}

	public static function &get_instance()
	{
		if(is_null(self::$_instance))
			self::$_instance = new kow_Framework;

		return self::$_instance;
	}

	public function run()
	{
		$this->route();
		$c = $this->dispatch();
		$c->render();
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
			if((isset($this->_vars[$category][$key]) and is_array($this->_vars[$category][$key])) or $force_array)
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

	public function replace($category, $key, $value = null)
	{
		if(is_null($value))
			$this->_vars[$category] = $key;
		else
			$this->_vars[$category][$key] = $value;
	}

	static function handle_by_plugin()
	{
		return kow_Framework::get_instance()->get('config', 'plugin_handled', false);
	}

	static function set_handle_by_plugin($name, $settings = null)
	{
		$kfw = kow_Framework::get_instance();

		if(is_array($settings) and isset($settings['use_controllers']))
		{
			if($controller = $kfw->get('router', 'action', false))
				$kfw->replace('router', 'controller', $controller);
			else if(isset($settings['default_controller']))
				$kfw->replace('router', 'controller', $settings['default_controller']);
			else
				$kfw->replace('router', 'controller', $kfw->get('config', 'default_controller'));

			if($params = $kfw->get('router', 'params', false))
			{
				$kfw->replace('router', 'action', current($params));
				array_shift($params);
				$kfw->replace('router', 'params', $params);
			}
			else if(isset($settings['default_view']))
				$kfw->replace('router', 'action', $settings['default_action']);
			else
				$kfw->replace('router', 'action', $kfw->get('config', 'default_action'));

			$kfw->set('config', 'plugin_use_controllers', true);
		}

		$kfw->set('config', 'plugin_handled', $name);
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
					throw new Exception('Le plugin "' . PLUGINS_PATH . $file . EXT . '" n\'existe pas.');
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
						if(isset($result[$function[1]]))
							$result[$function[1]] += $function[0]::$function[1]($arguments);
						else
							$result[$function[1]] = $function[0]::$function[1]($arguments);
				}
				else
				{
					if(function_exists($function))
						if(isset($result[$function]))
							$result[$function] += $function($arguments);
						else
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
            	$controller = $url[0];

            if(!empty($url[1]))
            	$action = $url[1];

            $params = array_slice($url, 2);
		}

		$this->set('router', array(
			'controller'	=> $controller,
			'action'		=> $action,
			'params' 		=> $params
		));

		self::run_hook('post_route', $this->get('router'));
	}

	public function dispatch()
	{
		$controller_path = ($this->get('config', 'plugin_handled', false)) ? PLUGINS_PATH . $this->get('config', 'plugin_handled', false) . '/' : CONTROLLERS_PATH;

		if(!file_exists($controller_path . $this->get('router', 'controller') . EXT))
			$this->set('router', 'controller', $this->get('config', 'default_controller'));

		require_once $controller_path . $this->get('router', 'controller') . EXT;
		$controller_class = 'Controller_' . ucfirst($this->get('router', 'controller'));

		// Todo : 404 error handler shouln't be dependent from an app controller and action 
		if(!in_array($this->get('router', 'action'), array_diff(
			get_class_methods($controller_class),
			get_class_methods(get_parent_class($controller_class)))
		))
			$this->set('router', 'action', $this->get('config', 'default_error404_view'));

		// To be modififed for HMVC
        $this->set('kow_Loader', 'instance', new kow_Loader);

        $c = new $controller_class;
        $this->set('kow_Controller', 'instance', $c);

        foreach($this->get('config', 'autoload_helpers') as $v)
        	$c->load()->helper($v, true);

        self::run_hook('pre_render');
        call_user_func_array(array($c, $this->get('router', 'action')), array($this->get('router', 'params')));

        return $c;
	}
}