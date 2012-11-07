<?php

if(!defined('KOWFRAMEWORK')) exit('You can\'t access this ressource.');

/**
 * Copyright (C) 2011-2012 Kevin Ryser et Simon Baehler <http://framework.koweb.ch>
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

define('RETURN_CURRENT', true);
define('RETURN_FIRST', true);

class kow_Model
{
	private $_db = null;
	private $_query = null;
	private $_index = 0;
	private $_queryLog = '';

	function __construct($database)
	{
		$db = kow_Framework::get_instance()->get('config', 'database');
		if(empty($db[$database]))
			throw new Exception('Les informations de connexion à la base de données "' . $database . '" n\'existe pas.');
			
		$db = $db[$database];

		try
		{
			$this->_db = new PDO('mysql:host=' . $db['host'] . ';port=' . $db['port'] . ';dbname=' . $db['database'] . ';', $db['username'], $db['password'], isset($db['options']) ? $db['options'] : array());
		}
		catch(PDOException $e)
		{
			throw new Exception('Erreur lors de la connexion à la base de données "' . $db['database'] . '" : ' . $e->getMessage());
		}

		return $this;
	}

	public function database()
	{
		return $this->_db;
	}

	public function newQuery($query)
	{
		$this->_queryLog = $query;
		$this->_index = 0;
		$this->_query = $this->_db->prepare($query);

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
        elseif(is_string($value))
            $param = PDO::PARAM_STR;
        else
            $param = FALSE;

		if(!$param || !$this->_query->bindValue(++$this->_index, $value, $param))
			$this->show_exception($this->_queryLog, 'index ' . $this->_index . ' valeur "'. $value .'"');

		return $this;
	}

	public function exec($current = false)
	{
		$this->_query->execute();

		if($this->_query->errorCode() != 0)
			$this->show_exception($this->_queryLog, $this->_query->errorInfo());

		$result = $this->_query->fetchAll(PDO::FETCH_OBJ);

		if($current AND sizeof($result) == 1)
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