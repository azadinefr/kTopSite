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

		if(!is_file(CONFIG_PATH))
			throw new Exception('Le fichier de configuration "' . CONFIG_PATH . '" n\'existe pas.');

		require_once CONFIG_PATH;

		if(empty($config) or !is_array($config))
			throw new Exception('Le fichier de configuration "' . CONFIG_PATH . '" est mal formaté.');

		if(@date_default_timezone_set(date_default_timezone_get()) === false)
			date_default_timezone_set($config['timezone']);

		$this->set('config', $config);
		$this->set('kow_Loader', 'instance', new kow_Loader);
		$this->load_hooks();

        foreach($this->get('config', 'autoload_helpers') as $v)
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

	public function load_hooks()
	{
		if($this->get('config', 'enable_hooks'))
		{
			foreach($this->get('config', 'hooks') as $file)
			{
				if(is_file(HOOKS_PATH . $file . EXT))
				{
					require_once HOOKS_PATH . $file . EXT;
					$url = explode('/', $file);
					$hook_class = end($url);
					$hook_class = 'Hook_' . ucfirst($hook_class);

					if(class_exists($hook_class, false))
					{
						if(method_exists($hook_class, 'load'))
							call_user_func(array($hook_class, 'load'), $this->get('config'));
;
						foreach(get_class_methods($hook_class) as $function)
							if(in_array($function, $this->_hook_list))
								$this->set('hooks', $function, array($hook_class, $function), true);
					}
				}
				else
					throw new Exception('Le fichier hook "' . HOOKS_PATH . $file . EXT . '" n\'existe pas.');
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
		$controller = $this->get('config', 'default_controller');
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

						$module_object->_my_infos = array(
							'module' => $module,
							'controller' => $controller,
							'action' => $action,
							'params' => $params
						);

						if(file_exists(MODULES_PATH . $module . SEP . 'config' . SEP . $module . EXT))
						{
							require_once MODULES_PATH . $module . SEP . 'config' . SEP . $module . EXT;
							$module_object->config = $config;
						}

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
					    	$this->set('kow_Templates', 'layout_content', $out);
						
					    else
					    	$this->set('kow_Templates', $render_name, $out);
					    
					    return;
					}
				}
			}
		}

		// Faire un hook?
		// manque header('HTTP/1.0 404 Not Found'); AUSSI !!!
		$this->get('kow_Loader', 'instance')->template('404', $this->get('config', 'show_404_master'));
	}

	public function view($template = null)
	{
		ob_start();

		if(is_file(THEMES_PATH . $this->get('config', 'theme_path') . SEP . 'build' . EXT))
			require_once THEMES_PATH . $this->get('config', 'theme_path') . SEP . 'build' . EXT;

		if($this->get('kow_Templates', null, false))
			extract($this->get('kow_Templates', null));

		if($template)
			$layout_content = $template;

		$theme = THEMES_PATH . $this->get('config', 'theme_path') . SEP . 'template' . EXT;
		if(!is_file($theme))
			throw new Exception('Le fichier de thème par défaut "' . $theme . '" n\'existe pas.');

		require_once $theme;

		$content = ob_get_contents();
		ob_end_clean();

        kow_Framework::run_hook('post_render', $content);
        die($content);
	}
}