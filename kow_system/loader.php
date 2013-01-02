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
	private $_controller = '';
	private $_action = '';
	private $_theme_path = '';
	private $_plugin_handled = false;
	private $_plugin_use_controllers = false;

	public function __construct()
	{
		$this->_kfw =& kow_Framework::get_instance();
		$this->_controller = $this->_kfw->get('router', 'controller');
		$this->_action = $this->_kfw->get('router', 'action');
		$this->_theme_path = $this->_kfw->get('config', 'theme_path');
		$this->_plugin_handled = $this->_kfw->get('config', 'plugin_handled', false);
		$this->_plugin_use_controllers = $this->_kfw->get('config', 'plugin_use_controllers', false);
	}

	public function library($name)
	{
		$name = explode(SEP, strtolower($name));

		if(sizeof($name) > 1)
		{
			$lib_name = ucfirst(end($name));
			$path = APP_PATH;
			foreach($name as $v)
				$path .= SEP . $v;
		}
		else
		{
			$lib_name = ucfirst($name[0]);
			$path = LIBS_PATH . $name[0];
		}

		if($this->_kfw->get('kow_Library', $lib_name, false))
			return $this->_kfw->get('kow_Library', $lib_name, false);

		require_once($path . EXT);

		if($settings)
			$lib = new $lib_name($settings);
		else
			$lib = new $lib_name;

		$this->_kfw->set('kow_Library', $lib_name, $lib);
		return $lib;
	}

	public function model($model, $database)
	{
		if(!$this->_kfw->get('kow_Model', 'included', false))
		{
			require_once SYS_PATH . 'model' . EXT;
			$this->_kfw->set('kow_Model', 'included', true);
		}

		if($model !== false)
		{
			if($this->_plugin_handled)
				$model_path = ($this->_plugin_use_controllers) ? PLUGINS_PATH . $this->_plugin_handled . '/models/' . $this->_controller . '/' . $this->_action . EXT : PLUGINS_PATH . $this->_plugin_handled . '/models/' . $this->_action . EXT;
			else
				$model_path = MODELS_PATH . $this->_controller . '/' . $this->_action . EXT;

			if(!empty($model))
				$model_path = MODELS_PATH . $this->_controller . '/' . $model . EXT;
			else
				$model = $this->_action;

			if(file_exists($model_path))
			{
				require_once $model_path;
				if(class_exists($model))
					$model_object = new $model($database);
			}
			else
				throw new Exception('Le modèle "' . $model_path . '" pour l\'action "' . $this->_action . '" du contrôleur "' . $this->_controller . ' n\'existe pas."');
		}
		else
			$model_object = new kow_Model($database);

		$this->_kfw->set('kow_Model', 'models', array($model => $model_object));
		return $model_object;
	}

	public function view($view = null)
	{
		if($this->_plugin_handled)
			$path_view = ($this->_plugin_use_controllers) ? PLUGINS_PATH . $this->_plugin_handled . '/views/' . $this->_controller : PLUGINS_PATH . $this->_plugin_handled . '/views';
		else
			$path_view = ($this->_plugin_handled) ? PLUGINS_PATH . $this->_plugin_handled . '/views' : VIEWS_PATH . $this->_controller;

		if(is_null($view))
			$view = $path_view . '/' . $this->_action . EXT;
		else
	    	$view = $path_view .  '/' . $view . EXT;

        if(!file_exists($view))
        {
        	if(DEBUG_MODE)
        		throw new Exception('La vue "' . $view . '" pour l\'action "' . $this->_action . '" du contrôleur "' . $this->_controller . '" n\'existe pas.');
        	else
        		$view = VIEWS_PATH . $this->_controller . '/' . kow_Framework::get_instance()->get('config', 'default_error404_view') . EXT;
        }

        $this->_kfw->get('kow_Controller', 'instance')->set_view($view);
	}

	public function helper($helper, $force_default_path = false)
	{
		if(!$force_default_path and $this->_plugin_handled)
			$helper_path =  PLUGINS_PATH . $this->_plugin_handled . '/helpers/' . $helper . EXT;
		else
			$helper_path  = HELPERS_PATH . $helper . EXT;

		if(file_exists($helper_path))
			require_once $helper_path;
		else
			throw new Exception('Le helper "' . $helper_path . '" n\'existe pas.');
	}

	// Est-ce qu'on supporte les thèmes handler par les plugins ??
	public function theme($layoutContent)
	{
		$theme = THEMES_PATH . $this->_theme_path . '/' . 'default' . EXT;

		if(!file_exists($theme))
			throw new Exception('Le fichier de thème par défaut "' . $theme . '" n\'existe pas.');

		if(file_exists(THEMES_PATH . $this->_theme_path . '/templates/' . $this->_controller . EXT))
			$theme = THEMES_PATH . $this->_theme_path . '/templates/' . $this->_controller . EXT;

		if(file_exists(THEMES_PATH . $this->_theme_path . '/templates/' . $this->_controller . '_' . $this->_action . EXT))
			$theme = THEMES_PATH . $this->_theme_path . '/templates/' . $this->_controller . '_' . $this->_action . EXT;

		return $theme;
	}
}