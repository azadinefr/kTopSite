<?php

if(!defined('SYS_PATH')) exit('You can\'t access this ressource.');

/**
 * New BSD License
 *
 * Copyright (C) 2011-2013 Kevin Ryser (http://framework.koweb.ch) All rights reserved
 * See the LICENSE file for the full license text.
 */

define('KOWFRAMEWORK', '1.0.37');

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

	private function __construct()
	{
		self::$_instance =& $this;

		if(!is_file(CONFIG_PATH . 'kowframework' . EXT))
			throw new Exception('Le fichier de configuration "' . CONFIG_PATH . 'kowframework' . EXT. '" n\'existe pas.');

		require_once CONFIG_PATH . 'kowframework' . EXT;

		if(empty($config) or !is_array($config))
			throw new Exception('Le fichier de configuration "' . CONFIG_PATH . 'kowframework' . EXT . '" est mal formaté.');

		if(@date_default_timezone_set(date_default_timezone_get()) === false)
			date_default_timezone_set($config['timezone']);

		$this->set('kow_Config', $config);
		$this->set('kow_Loader', 'instance', new kow_Loader);
		$this->load_plugins();

        foreach($this->get('kow_Config', 'autoload_helpers') as $v)
        	$this->get('kow_Loader', 'instance')->helper($v);
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

		$router = $this->get('router');
		self::run_hook('post_route', $router);
		
		$module_url = $router['controller'] . SEP . $router['action'];
		$this->run_module($module_url, $router['params']);
		$this->view();
	}

	public function set($category, $key, $value = null, $force_array = false)
	{
		if(is_null($value))
		{
			if(isset($this->_vars[$category]))
				if(is_array($this->_vars[$category]))
					if(is_array($key))
						$this->_vars[$category] = array_merge($this->_vars[$category], $key);
					else
						$this->_vars[$category][] = $key;
				else
					$this->_vars[$category] = array($this->_vars[$category], $key);
			else
				$this->_vars[$category] = $key;
		}
		else
		{
			if(isset($this->_vars[$category][$key]))
				if(is_array($this->_vars[$category][$key]))
					if(is_array($value) and !$force_array)
						$this->_vars[$category][$key] = array_merge($this->_vars[$category][$key], $value);
					else
						$this->_vars[$category][$key][] = $value;
				else
					$this->_vars[$category][$key] = array($this->_vars[$category][$key], $value);
			else
				if($force_array)
					$this->_vars[$category][$key] = array($value);
				else
					$this->_vars[$category][$key] = $value;
		}
	}

	public function get($category, $key = null, $show_exception = true)
	{
		if(is_null($key) and isset($this->_vars[$category]))
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

	public function load_plugins()
	{
		if($this->get('kow_Config', 'enable_plugins'))
		{
			foreach($this->get('kow_Config', 'plugins') as $file_path)
			{
				if(is_file(PLUGINS_PATH . $file_path . EXT))
				{
					require_once PLUGINS_PATH . $file_path . EXT;
					$file = explode(SEP, $file_path);
					$plugin_class = 'Plugin_' . ucfirst(strtolower(end($file)));

					if(class_exists($plugin_class, false))
					{
						if(is_file(PLUGINS_PATH . $file_path . SEP . 'config' . EXT))
							require_once PLUGINS_PATH . $file_path . SEP . 'config' . EXT;

						if(method_exists($plugin_class, '_load'))
							call_user_func(array($plugin_class, '_load'), isset($config) ? $config : null);
;
						foreach(get_class_methods($plugin_class) as $function)
							if($function[0] == '_')
								$this->set('hooks', substr($function, 1), array($plugin_class, $function), true);
					}
				}
				else
					throw new Exception('Le plugin "' . PLUGINS_PATH . $file_path . EXT . '" n\'existe pas.');
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
					{
						if(isset($result[$function[1]]))
							if(is_array($result[$function[1]]))
								$result[$function[1]][] = $function[0]::$function[1]($arguments);
							else
								$result[$function[1]] = array($result[$function[1]], $function[0]::$function[1]($arguments));
						else
							$result[$function[1]] = $function[0]::$function[1]($arguments);
					}
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
		$controller = $this->get('kow_Config', 'default_controller');
		$action = 'index';
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
	}

	public function run_module($module)
	{
		if(($pos = strpos($module, '/')) !== false)
		{
			$action = substr($module, $pos + 1);
			$module = substr($module, 0, $pos);
		}

		$controller = $module;
		if(!isset($action)) $action = 'index';
		$params = current(array_slice(func_get_args(), 1));
		$ready = false;

		if(is_dir(MODULES_PATH . $module))
		{
			if(!isset($action))
			{
				$controller = $module;
				$action = 'index';
			}
			else
			{
				if(is_file(MODULES_PATH . $module . SEP . 'controllers' . SEP . $action . EXT))
				{
					$controller = $action;
					if(!empty($params))
						$action = array_shift($params);
					else
						$action = 'index';

					$ready = true;
				}
			}

			if(!$ready)
				if(is_file(MODULES_PATH . $module . SEP . 'controllers' . SEP . $controller . EXT))
					$ready = true;

			if($ready)
			{
				require_once MODULES_PATH . $module . SEP . 'controllers' . SEP . $controller . EXT;
				$controller_class = 'Controller_' . ucfirst($controller);

				if(class_exists($controller_class))
				{
					if(in_array($action, array_diff(get_class_methods($controller_class),
						get_class_methods(get_parent_class($controller_class)))
					))
					{
						$module_object = new $controller_class;
						if(!$this->get('kow_Modules', $module, false))
							$this->set('kow_Modules', $module, $module_object);
						else
							throw new Exception('Impossible de lancer deux fois le même module. En tout cas pour l\'instant.');

						$module_object->my_infos(array(
							'module' => $module,
							'controller' => $controller,
							'action' => $action,
							'params' => $params
						));

						if(file_exists(MODULES_PATH . $module . SEP . 'config' . SEP . 'config' . EXT))
							$module_object->config = $module_object->load_my()->config('config');

						if(isset($module_object->config) and isset( $module_object->config['autoload_helpers']))
					    	if($helpers = $module_object->config['autoload_helpers'])
					    		foreach($helpers as $helper)
					    			$module_object->load()->helper($helper, $module);

					    $out = call_user_func_array(array($module_object, $action), array($params));

					    if($module_object->is_script())
					    	die($out);

					    if($out === null)
					    	$out = $module_object->render();

					    if($template_name = $module_object->template_name())
					    	$render_name = $template_name;
					    else
					    	$render_name = $module;

					    if($module == $this->get('router', 'controller'))
					    	$this->set_template_var('layout_content', $out);
						
					    else
					    	$this->set_template_var($render_name, $out);
					    
					    return;
					}
				}
			}
		}

		header("HTTP/1.0 404 Not Found");
		$this->get('kow_Loader', 'instance')->template('404', $this->get('kow_Config', 'show_404_master'));
	}

	public function set_template_var($name, $var)
	{
	   	$this->set('kow_Templates', $name, $var);
	}

	public function view($template = null)
	{
		ob_start();

		if(is_file(THEMES_PATH . $this->get('kow_Config', 'theme_path') . SEP . 'build' . EXT))
			require_once THEMES_PATH . $this->get('kow_Config', 'theme_path') . SEP . 'build' . EXT;

		if($this->get('kow_Templates', null, false))
			extract($this->get('kow_Templates', null));

		if($template)
			$layout_content = $template;

		$theme = THEMES_PATH . $this->get('kow_Config', 'theme_path') . SEP . 'template' . EXT;
		if(!is_file($theme))
			throw new Exception('Le fichier de thème par défaut "' . $theme . '" n\'existe pas.');

		require_once $theme;

		$content = ob_get_contents();
		ob_end_clean();

        kow_Framework::run_hook('post_render', $content);
        die($content);
	}
}