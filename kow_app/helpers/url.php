<?php

if(!defined('KOWFRAMEWORK')) exit('You can\'t access this ressource.');

$kfw_config = kow_Framework::get_instance()->get('kow_Config');
define('THEME_PATH', $kfw_config['theme_path']);
define('URL_REWRITING_ENABLED', $kfw_config['enable_url_rewriting']);

function url($url)
{
	if(URL_REWRITING_ENABLED)
		echo BASE_URL . $url;
	else
		echo 'index.php?p=' . $url;
}

function favicon($icon)
{
	$ext = explode('.', $icon);
	$mime = (strtolower(end($ext)) == 'png') ? 'png' : 'x-icon';
	echo '<link rel="shortcut icon" type="image/' . $mime . '" href="' . BASE_URL . THEMES_PATH . THEME_PATH . '/images/' . $icon . '" />' . PHP_EOL;
}

function img($image)
{
	echo BASE_URL . THEMES_PATH . THEME_PATH . '/images/' .  $image;
}

function redirect($url)
{
	$location = 'Location: ';
	if(strpos($url, 'http') !== false)
		$location .= $url;
	else
	{
		if(URL_REWRITING_ENABLED)
			$location .= BASE_URL . $url;
		else
			$location .= $url;
	}

	header($location);
}

function css($name)
{
	echo '<link href="' . BASE_URL . THEMES_PATH . THEME_PATH . '/css/' . $name . '" rel="stylesheet">' . PHP_EOL;
}

function js($name)
{
	echo '<script src="' . BASE_URL . THEMES_PATH . THEME_PATH . '/jscripts/' . $name . '"></script>' . PHP_EOL;
}