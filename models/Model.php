<?php

namespace Models;


class Model
{
	protected $db;
	
	public function __construct()
	{
		$this->db = Model::getDB();
	}

	static public function getDB()
	{
		$db = new HaloDb(array(
				'host'=>'dbstock',
				'user'=>'stock',
				'pass'=>'stock@1312',
				'dbname'=>'stockdb'
		));
		$db->query('SET NAMES utf8');
		return $db;
	}
}
