<?php

if(!defined('KOWFRAMEWORK')) exit('You can\'t access this ressource.');

class Controller_Page extends kow_Controller
{
	public function index()
	{
	}

	public function error404()
	{
		header('HTTP/1.0 404 Not Found');
	}
}