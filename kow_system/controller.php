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
	private $_vars = array();
	private $_request = array();
	private $_view = null;
	private $_rendered = false;
	private $_is_script = false;
	private $_template_name = null;

	public function __construct()
	{
		$this->_kfw =& kow_Framework::get_instance();
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

	public function template_name($name = null)
	{
		if($name)
			$this->_template_name = $name;
		else
			return $this->_template_name; 
	}

	public function request($method = 'request')
	{
		$method = strtolower($method);
		if(isset($this->_request[$method]))
			return $this->_request[$method];

		$this->_request[$method] = new StdClass;
    	if($method == 'POST')
    		foreach($_POST as $k => $v)
        		$this->_request[$method]->$k = $v;
        else if($method == 'GET')
    		foreach($_GET as $k => $v)
        		$this->_request[$method]->$k = $v;
  		else
        	foreach($_REQUEST as $k => $v)
        		$this->_request[$method]->$k = $v;

		return $this->_request[$method];
	}

	public function no_render()
	{
		$this->is_script(true);
	}

	public function is_script($is_script = null)
	{
		if(is_bool($is_script))
			$this->_is_script = $is_script;
		else
			return $this->_is_script;
	}

	public function set_view($view)
	{
		$this->_view = $view;
	}

	public function load()
	{
		return $this->_kfw->get('kow_Loader', 'instance');
	}

	public function load_my()
	{
		return $this->_kfw->get('kow_Loader', 'instance')->set_current_module($this->_my_infos);
	}

	public function load_his($module)
	{
		$module_info = $this->_my_infos;
		$module_info['module'] = $module;
		return $this->_kfw->get('kow_Loader', 'instance')->set_current_module($module_info);
	}

	public function kfw()
	{
		return $this->_kfw;
	}

	public function model($database, $model = null)
	{
		$models = $this->_kfw->get('kow_Model', 'models', false);
		if(!empty($models[$model]))
			$model = $models[$model];
		else
			$model = $this->load_my()->model($database, $model);

		return $model;
	}

	public function set_template_var($name, $var)
	{
		$this->kfw()->set_template_var($name, $var);
	}

	public function render()
	{
        $this->load_my()->view($this->_view);
        extract($this->_vars);

        ob_start();
		require_once $this->_view;
		$content = ob_get_contents();
		ob_end_clean();

        $this->_rendered = true;
        return $content;
	}
}