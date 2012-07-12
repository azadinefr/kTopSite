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

class kow_Model
{
	private $_db = null;
	private $_req = '';
	private $_properties = 0;
	private $_compare = array('<', '>', 'BETWEEN');

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

	public function find($table, $query = array())
	{
		$req = 'SELECT ';

		if(!empty($query['fields']))
		{
		 	if(is_array($query['fields']))
		 		$req .= implode(', ', $query['fields']);
		 	else
		 		$req .= $query['fields'];
		}
		else
			$req .= '*';

	 	$req .= ' FROM ' . $table . ' ';

		if(!empty($query['join']))
			foreach($query['join'] as $k => $v)
				$req .= 'INNER JOIN ' . $k . ' ON ' . $v . ' ';

	 	if(!empty($query['conditions']))
	 	{
	 		$req .= 'WHERE ';

	 		if(!is_array($query['conditions']))
 				$req .= $query['conditions'];
 			else
	 			$cond = $this->parseConditions($query['conditions']);

	 		if(!empty($query['operator']))
	 			$req .= implode(' ' . $query['operator'] . ' ', $cond);
	 		else if(!empty($cond))
	 			$req .= implode(' AND ', $cond);
		 }

		if(!empty($query['order']))
			$req .= ' ORDER BY ' . $query['order'];

		if(!empty($query['limit']))
			$req .= ' LIMIT ' . $query['limit'];

		$p = $this->_db->prepare($req);
		$p->execute();

		if($p->errorCode() != 0)
			$this->show_exception($req, $p->errorInfo());

		echo $req;

		return $p->fetchAll(PDO::FETCH_OBJ);
	}

	public function findFirst($table, $query)
	{
		return current($this->find($table, $query));
	}

	public function findCount($table, $query)
	{
		$req = $this->findFirst(array(
			'fields' => ' COUNT(' . $query['fields'] . ') AS count',
			'conditions' => isset($query['conditions']) ? $query['conditions'] : ''
		));

		if($p->errorCode() != 0)
			$this->show_exception($req, $p->errorInfo());

		return $req->count;
	}

	public function add($table, $query)
	{
		$req = 'INSERT INTO ' . $table;

		$keys = array();
		$values = array();
		foreach($query['fields'] as $k => $v)
		{
			if(!is_numeric($v))
	 			$v = '"' . mysql_real_escape_string($v) . '"';

			$keys[] = $k;
			$values[] = $v;
		}

		$req .= ' (' . implode(', ', $keys) . ') ';
		$req .= ' VALUES ';
		$req .= ' (' . implode(', ', $values) . ')' ;

		$p = $this->_db->prepare($req);
		$p->execute();

		if($p->errorCode() != 0)
			$this->show_exception($req, $p->errorInfo());
	}

	public function update($table, $query)
	{
		$req = 'UPDATE ' . $table . ' SET ';

		$fields = array();
		foreach($query['fields'] as $k => $v)
		{
			if(!is_numeric($v) && strpos($v, '+') === false && strpos($v, '-') === false)
	 			$v = '"' . mysql_real_escape_string($v) . '"';
	 		$fields[] = $k . ' = ' . $v;
		}

		$req .= implode(' , ', $fields);
		
		if(!empty($query['conditions']))
	 	{
	 		$req .= ' WHERE ';

	 		if(!is_array($query['conditions']))
 				$req .= $query['conditions'];
 			else
	 			$cond = $this->parseConditions($query['conditions']);

	 		if(!empty($query['operator']))
	 			$req .= implode(' ' . $query['operator'] . ' ', $cond);
	 		else if(!empty($cond))
	 			$req .= implode(' AND ', $cond);
		}

		$p = $this->_db->prepare($req);
		$p->execute();

		if($p->errorCode() != 0)
			$this->show_exception($req, $p->errorInfo());
	}

	public function delete($table, $query)
	{
		$req = 'DELETE FROM ' . $table . ' WHERE ';

		$cond = array();
		foreach($query['conditions'] as $k => $v)
	 	{
		 	if(!is_numeric($v))
	 			$v = '"' . mysql_real_escape_string($v) . '"';
			
			$cond[] = $k . ' = ' . $v;
		}

		if(!empty($query['operator']))
	 		$req .= implode(' ' . $query['operator'] . ' ', $cond);
	 	else
	 		$req .= implode(' AND ', $cond);

		$p = $this->_db->prepare($req);
		$p->execute();

		if($p->errorCode() != 0)
			$this->show_exception($req, $p->errorInfo());
	}

	public function parseConditions($conditions)
	{
		$cond = array();
		foreach($conditions as $k => $v)
		{
			$compare = false;
			foreach($this->_compare as $c)
			{
				if(strpos($v, $c) !== false)
				{
					$compare = true;
					break;
				}
			}

			if(!is_numeric($v) && !$compare)
				$v = '"' . mysql_real_escape_string($v) . '"';

			if($compare)
				$cond[] = $k . $v;
			else
				$cond[] = $k . ' = ' . $v;
		}

 		return $cond;
	}

	public function show_exception($req, $errorInfo)
	{
		throw new Exception('Erreur lors de l\'exécution de la requête SQL : ' . $req . '<br /><br />
			<strong>Code :</strong> ' . $errorInfo[1] . '<br />
			<strong>Message :</strong> ' . $errorInfo[2]);
	}
}