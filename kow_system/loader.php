<?php

if(!defined('SYS_PATH')) exit('You can\'t access this ressource.');

/**
 * New BSD License
 *
 * Copyright (C) 2011-2013 Kevin Ryser (http://framework.koweb.ch) All rights reserved
 * See the LICENSE file for the full license text.
 */

class kow_Loader
{
	private $_kfw = null;
	private $_module = null;
	private $_theme_path = '';

	public function __construct()
	{
		$this->_kfw =& kow_Framework::get_instance();
		$this->_theme_path = $this->_kfw->get('kow_Config', 'theme_path');
	}

	public function set_current_module($module)
	{
		$this->_module = $module;
		return $this;
	}

	public function config($config_file, $sub_array = false)
	{
		if($this->_module['module'])
			$path = MODULES_PATH . $this->_module['module'] . SEP . 'config' . SEP . $config_file . EXT;
		else
			$path = CONFIG_PATH . $config_file . EXT;

		if(is_file($path))
		{
			if ($path == CONFIG_PATH . 'kowframework' . EXT)
				$config = $this->_kfw->get('kow_Config');
			else
				require $path;
			
			if (!isset($config) or !is_array($config))
				throw new Exception('Le fichier de configuration "' . $path . '" ne contient pas un tableau (array) valide.');
		}
		else
			throw new Exception('Le fichier de configuration "' . $path . '" n\'existe pas.');

		$this->_module = null;

		if ($sub_array)
			return array($config_file => $config);
		else
			return $config;
	}

	public function helper($helper)
	{
		if(is_array($helper))
			foreach($helper as $h)
				$this->helper($h);

		$helper = explode(SEP, $helper);
		$helper = end($helper);

		if($this->_module['module'])
			$path = MODULES_PATH . $this->_module['module'] . SEP . 'helpers' . SEP . $helper . EXT;
		else
			$path = HELPERS_PATH . $helper . EXT;

		if(is_file($path))
			require_once $path;
		else
			throw new Exception('Le helper "' . $path . '" n\'existe pas.');

		$this->_module = null;
	}

	public function library($library)
	{
		if($this->_module)
			$path = MODULES_PATH . $this->_module['module'] . SEP . 'libraries' . SEP . strtolower($library) . EXT;
		else
			$path = LIBS_PATH . strtolower($library) . EXT;

		$lib_name = explode(SEP, $library);
		$lib_name = end($lib_name);
		$lib_index = ($this->_module) ? $this->_module['module'] . SEP . $lib_name : $lib_name;

		if(func_num_args() > 1)
			$params = current(array_slice(func_get_args(), 1));

		if($this->_kfw->get('kow_Library', $lib_index, false))
			return $this->_kfw->get('kow_Library', $lib_index, false);

		if(class_exists($lib_name))
			throw new Exception('Une classe du même nom "' . $lib_name . '" est déjà instanciée. Vivement les namespaces.');

		if (!is_file($path))
			throw new Exception('La librairie "' . $path . '" n\'existe pas.');

		require_once($path);

		if(isset($params))
			$lib = new $lib_name($params);
		else
			$lib = new $lib_name;

		$this->_kfw->set('kow_Library', $lib_index, $lib);
		$this->_module = null;

		return $lib;
	}

	public function model($database, $model)
	{
		if(!$this->_kfw->get('kow_Model', 'included', false))
		{
			require_once SYS_PATH . 'model' . EXT;
			$this->_kfw->set('kow_Model', 'included', true);
		}

		if($model !== false)
		{
			$model_path = MODULES_PATH . $this->_module['module'] . SEP . 'models' . SEP . $this->_module['controller'] . SEP . $this->_module['action'] . EXT;
			
			if($this->_module['module'] == $this->_module['controller'])
				if(is_file(MODULES_PATH . $this->_module['module'] . SEP . 'models' . SEP . $this->_module['action'] . EXT))
					$model_path = MODULES_PATH . $this->_module['module'] . SEP . 'models' . SEP . $this->_module['action'] . EXT;

			if(!empty($model))
				$model_path = MODELS_PATH . $this->_module['module'] . SEP . $model . EXT;
			else
				$model =  $this->_module['action'];

			if(is_file($model_path))
			{
				require_once $model_path;
				if(class_exists($model))
					$model_object = new $model($database);
			}
			else
				throw new Exception('Le modèle "' . $model_path . '" pour l\'action "' . $this->_module['action'] . '" du contrôleur "' . $this->_module['controller'] . '" n\'existe pas.');
		}
		else
			$model_object = new kow_Model($database);

		$this->_kfw->set('kow_Model', 'models', array($model => $model_object));
		$this->_module = null;
		return $model_object;
	}

	public function view($view = null)
	{
		if(is_null($view))
		{
			$view = MODULES_PATH . $this->_module['module'] . SEP . 'views' . SEP . $this->_module['controller'] . SEP . $this->_module['action'] . EXT;
			
			if($this->_module['module'] == $this->_module['controller'])
				if(is_file(MODULES_PATH . $this->_module['module'] . SEP . 'views' . SEP . $this->_module['action'] . EXT))
					$view = MODULES_PATH . $this->_module['module'] . SEP . 'views' . SEP . $this->_module['action'] . EXT;
		}
		else
		{

	    	$view = MODULES_PATH . $this->_module['module'] . SEP . 'views' . SEP . $this->_module['controller'] . SEP . $view . EXT;

			if($this->_module['module'] == $this->_module['controller'])
				if(is_file(MODULES_PATH . $this->_module['module'] . SEP . 'views' . SEP . $view . EXT))
					$view = MODULES_PATH . $this->_module['module'] . SEP . 'views' . SEP . $view . EXT;
	    }

        if(!is_file($view))
        {
        	if(DEBUG_MODE)
        		throw new Exception('La vue "' . $view . '" pour l\'action "' . $this->_module['action'] . '" du contrôleur "' . $this->_module['controller'] . '" n\'existe pas.');
        	else
        		$this->template('404', $this->_kfw->get('kow_Config', 'show_404_master'));
        }

        $this->_kfw->get('kow_Modules', $this->_module['module'])->set_view($view);
        $this->_module = null;
	}

	public function template($template, $include_in_master = true)
	{
		if(!$include_in_master)
			if(is_file(THEMES_PATH . $this->_kfw->get('kow_Config', 'theme_path') . SEP . 'build' . EXT))
				require_once THEMES_PATH . $this->_kfw->get('kow_Config', 'theme_path') . SEP . 'build' . EXT;

		$template = THEMES_PATH . $this->_theme_path . SEP . 'templates' . SEP . $template . EXT;

		ob_start();

		if($this->_kfw->get('kow_Templates', null, false))
			extract($this->_kfw->get('kow_Templates', null, false));

		require_once $template;
		$content = ob_get_clean();

        if(!$include_in_master)
        	die($content);
 
 		$this->_kfw->view($content);
 		$this->_module = null;
 	}
}