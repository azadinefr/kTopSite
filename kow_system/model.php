<?php

if(!defined('KOWFRAMEWORK')) exit('You can\'t access this ressource.');

/**
 * New BSD License
 *
 * Copyright (C) 2011-2013 Kevin Ryser (http://framework.koweb.ch) All rights reserved
 * See the LICENSE file for the full license text.
 */

define('RETURN_CURRENT', true);
define('RETURN_FIRST', true);

class kow_Model
{
	private $_db = null;
	private $_query = null;
	private $_index = 0;
	private $_queryLog = '';

	public function __construct($database)
	{
		$connections = kow_Framework::get_instance()->get('kow_Model', 'connections', false);

		if($connections and !empty($connections[$database]))
			$this->_db = $connections[$database];
		else
		{
			$db = kow_Framework::get_instance()->get('config', 'database', false);
			if(!isset($db[$database]) or !is_array($db[$database]))
				throw new Exception('Les informations de connexion à la base de données "' . $database . '" n\'existe pas.');

			$db = $db[$database];

			try
			{
				$this->_db = new PDO('mysql:host=' . $db['host'] . ';port=' . $db['port'] . ';dbname=' . $database . ';', $db['username'], $db['password'], isset($db['options']) ? $db['options'] : array());

				kow_Framework::get_instance()->set('kow_Model', 'connections', array($database => $this->_db));
			}
			catch(PDOException $e)
			{
				throw new Exception('Erreur lors de la connexion à la base de données "' . $db['database'] . '" : ' . $e->getMessage());
			}
		}

		return $this;
	}

	public function &database()
	{
		return $this->_db;
	}

	public function newQuery($query)
	{
		$this->_queryLog = $query;
		$this->_index = 0;
		$this->_query = $this->_db->prepare($query);

		if (func_num_args() > 1)
		{
			$params = array_slice(func_get_args(), 1);
			foreach ($params as $param)
				$this->bind($param);
		}

		return $this;
	}

	public function bindArray($values)
	{
		if(is_array($values))
			foreach($values as $value)
				$this->bind($value);

		return $this;
	}

	public function bind($value)
	{
        if(is_int($value))
            $param = PDO::PARAM_INT;
        elseif(is_bool($value))
            $param = PDO::PARAM_BOOL;
        elseif(is_null($value))
            $param = PDO::PARAM_NULL;
        else
            $param = PDO::PARAM_STR;

		if(!$this->_query->bindValue(++$this->_index, $value, $param))
			$this->show_exception($this->_queryLog, 'index ' . $this->_index . ' valeur "'. $value .'"');

		return $this;
	}

	public function exec($current = false)
	{
		$this->_query->execute();

		if($this->_query->errorCode() != 0)
			$this->show_exception($this->_queryLog, $this->_query->errorInfo());

		$result = $this->_query->fetchAll(PDO::FETCH_OBJ);

		if(($current and sizeof($result) == 1) or sizeof($result) == 1)
			return current($result);
		else
			return $result;
	}

	public function show_exception($_queryLog, $errorInfo)
	{
		if(is_array($errorInfo))
			throw new Exception('Erreur lors de l\'exécution de la requête SQL : ' . $_queryLog . '<br /><br />
				<strong>Code :</strong> ' . $errorInfo[1] . '<br />
				<strong>Message :</strong> ' . $errorInfo[2]);
		else
			throw new Exception('Erreur lors de l\'exécution de la requête SQL : ' . $_queryLog . '<br /><br />
				<strong>Impossible de lier le paramètre</strong> ' . $errorInfo);
	}
}