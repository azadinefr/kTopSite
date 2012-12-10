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

class kow_Loader
{
	private $_controller = '';
	private $_action = '';
	private $_theme_path = '';
	private $_plugin_handled = false;
	private $_plugin_use_controllers = false;

	public function __construct()
	{
		$kwf = kow_Framework::get_instance();
		$this->_controller = $kwf->get('router', 'controller');
		$this->_action = $kwf->get('router', 'action');
		$this->_theme_path = $kwf->get('config', 'theme_path');
		$this->_plugin_handled = $kwf->get('config', 'plugin_handled', false);
		$this->_plugin_use_controllers = $kwf->get('config', 'plugin_use_controllers', false);
	}

	public function model($model, $database)
	{
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
					return new $model($database);
			}
			else
				throw new Exception('Le modèle "' . $model_path . '" pour l\'action "' . $this->_action . '" du contrôleur "' . $this->_controller . ' n\'existe pas."');
		}

		return new kow_Model($database);
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

        kow_Controller::get_instance()->set_view($view);
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