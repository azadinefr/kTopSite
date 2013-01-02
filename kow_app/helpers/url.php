<?php

if(!defined('KOWFRAMEWORK')) exit('You can\'t access this ressource.');

$kfw_config = kow_Framework::get_instance()->get('config');
define('THEME_PATH', $kfw_config['theme_path']);
define('URL_REWRITING_ENABLED', $kfw_config['enable_url_rewriting']);

function url_path($url)
{
	if(URL_REWRITING_ENABLED)
		echo BASE_URL . $url;
	else
		echo 'index.php?p=' . $url;
}

function img_path($image)
{
	echo BASE_URL . THEMES_PATH . THEME_PATH . '/images/' .  $image;
}

function redirect($url)
{
	if(URL_REWRITING_ENABLED)
		header('Location: ' . BASE_URL . $url);
	else
		header('Location: index.php?p=' . $url);
}

function css($name)
{
	echo '<link href="' . BASE_URL . THEMES_PATH . THEME_PATH . '/css/' . $name . '" rel="stylesheet">' . PHP_EOL;
}

function js($name)
{
	echo '<script src="' . BASE_URL . THEMES_PATH . THEME_PATH . '/jscripts/' . $name . '"></script>' . PHP_EOL;
}