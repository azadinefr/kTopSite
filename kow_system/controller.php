<?php

if(!defined('SYS_PATH')) exit('You can\'t access this ressource.');

/**
 * New BSD License
 *
 * Copyright (C) 2011-2013 Kevin Ryser (http://framework.koweb.ch) All rights reserved
 * See the LICENSE file for the full license text.
 */

class kow_Controller
{
	private $_kfw = null;
	private $_load = null;
	private $_vars = array();
	private $_model = array();
	private $_request = array();
	private $_view = null;
	private $_rendered = false;

	public function __construct()
	{
		$this->_kfw =& kow_Framework::get_instance();
		$this->_load =& $this->_kfw->get('kow_Loader', 'instance');
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
		if($method != 'POST' and $method != 'GET')
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

        $this->load()->view($this->_view);
        extract($this->_vars);

        ob_start();
		require_once $this->_view;
		$layoutContent = ob_get_clean();

		ob_start();
		require_once $this->load()->theme($layoutContent);
        $content = ob_get_clean();
        
        kow_Framework::run_hook('post_render', $content);

        echo $content;
        $this->_rendered = true;
	}
}