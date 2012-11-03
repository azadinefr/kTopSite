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

class kow_Controller
{
	private static $_instance = null;
	private $_load = null;
	private $_vars = array();
	private $_model = array();
	private $_request = array();
	private $_view = null;
	private $_rendered = false;

	public function __construct()
	{
		self::$_instance = &$this;
		$this->_load = new kow_Loader;
	}

	public static function &get_instance()
	{
		return self::$_instance;
	}

	public function __set($key, $value)
	{
		$this->_vars[$key] = $value;
	}

	public function __get($key)
	{
		if(isset($this->_vars[$key]))
			return $this->_vars[$key];
		else
			throw new Exception('Le paramètre "' . $key . '" n\'existe pas.');
	}

	public function request($method)
	{
		$method = strtoupper($method);
		if($method != 'POST' AND $method != 'GET')
			throw new Exception('Méthode "' . $method . '" iconnue. Choisir entre "GET" ou "POST".');

		if(empty($this->_request[$method]))
		{
			$this->_request[$method] = new StdClass;
        	if($method == 'POST')
        		foreach($_POST as $k => $v)
            		$this->_request[$method]->$k = $v;
            else
        		foreach($_GET as $k => $v)
            		$this->_request[$method]->$k = $v;
		}
			
		return $this->_request[$method];
	}

	public function no_render()
	{
		$this->_rendered = true;
	}

	public function set_view($view)
	{
		$this->_view = $view;
	}

	public function load()
	{
		return $this->_load;
	}

	public function model($database, $model = null)
	{
		if($model === false)
			$index = 0;
		else if($model === null)
			$index = $this->_view;
		else
			$index = $model;

		if(empty($this->_model[$database][$index]))
			$this->_model[$database][$index] = $this->_load->model($model, $database);

		return $this->_model[$database][$index];
	}

	public function render()
	{
        if($this->_rendered)
            return;

        $this->_load->view($this->_view);
        extract($this->_vars);

        ob_start();
		require_once $this->_view;
		$layoutContent = ob_get_clean();

        $content = $this->_load->theme($layoutContent);
        
        kow_Framework::run_hook('post_render', $content);

        echo $content;
        $this->_rendered = true;
	}
}