<?php

namespace Models;


class IndicatorModel extends Model
{
    public static $table = 'indicator';

	public function __construct()
	{
		parent::__construct();
	}

    public function getInfo($noLimitSql, $sql)
    {
        $db = $this->db;
        return $db->getQuery($noLimitSql, $sql);
    }

}