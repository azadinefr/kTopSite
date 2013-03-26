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

class index extends kow_Model
{
	function __construct($database)
	{
		parent::__construct($database);
	}

	public function api_methods()
	{
		return $this->newQuery('SELECT * FROM api_methods')->exec();
	}

	public function delete_methods()
	{
		return $this->newQuery('DELETE FROM api_methods')->exec();
	}

	public function save_method($object)
	{
		$req = $this->newQuery('INSERT INTO api_methods (id, file, visibility, f_return, function, settings, description, source) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
		$req->bind(++$object->id);
		$req->bind($object->file);
		$req->bind($object->visibility);
		$req->bind($object->f_return);
		$req->bind($object->function);
		$req->bind($object->settings);
		$req->bind($object->description);
		$req->bind($object->source);
		return $req->exec();
	}
}