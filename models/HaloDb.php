<?php

namespace Models;


class HaloDb extends \ezSQL_mysql
{
    public function __construct($dbconfig)
    {
        parent::ezSQL_mysql($dbconfig['user'], $dbconfig['pass'],
            $dbconfig['dbname'], $dbconfig['host']);
    }

    public function getVarByCondition($table, $condition, $varName)
    {
        $sql = sprintf('SELECT %s FROM %s', $varName, $table);
        if (!empty($condition)) {
            $sql .= ' WHERE ' . $condition;
        }
        return $this->get_var($sql);
    }

    public function getCountByCondition($table, $condition)
    {
        $sql = sprintf("SELECT COUNT(*) FROM %s WHERE %s", $table, $condition);
        return intval($this->get_var($sql));
    }

    public function getCountByGroup($group, $table, $condition)
    {
        $sql = sprintf("SELECT %s,COUNT(*) as count FROM %s WHERE %s", $group, $table, $condition);
        return $this->get_results($sql, ARRAY_A);
    }

    public function getDistinctByCondition($table, $condition, $distinct)
    {
        $sql = sprintf("SELECT DISTINCT %s FROM %s", $distinct, $table, $condition);
        if (!empty($condition)) {
            $sql .= ' WHERE ' . $condition;
        }
        return $this->get_col($sql);
    }

    public function getRowByCondition($table, $condition, $fields = '')
    {
        if (empty($fields)) {
            $sql = sprintf("SELECT * FROM %s WHERE %s", $table, $condition);
        } else {
            $sql = sprintf("SELECT %s FROM %s WHERE %s", $fields, $table, $condition);
        }
        return $this->get_row($sql, ARRAY_A);
    }

    public function getColByCondition($table, $condition, $colName)
    {
        $sql = sprintf("SELECT %s FROM %s WHERE %s", $colName, $table, $condition);
        return $this->get_col($sql);
    }

    public function getResultsByCondition($table, $condition = '', $fields = '')
    {
        if (empty($fields)) {
            if (empty($condition)) {
                $sql = sprintf('SELECT * FROM %s', $table);
            } else {
                $sql = sprintf('SELECT * FROM %s WHERE %s', $table, $condition);
            }
        } else {
            if (empty($condition)) {
                $sql = sprintf('SELECT %s FROM %s', $fields, $table);
            } else {
                $sql = sprintf('SELECT %s FROM %s WHERE %s', $fields, $table, $condition);
            }
        }
        return $this->get_results($sql, ARRAY_A);
    }

    //return 插入id
    public function insertTable($table, $data)
    {
        if (is_array($data)) {
            $list = array();
            foreach ($data as $k => $v) {
                $list[] = sprintf("%s='%s'", $k, $this->dbencode($v));
            }
            $count = count($list);
            if (count($list) > 0) {
                $sql = sprintf('INSERT INTO %s SET %s', $table, implode(',', $list));
                $result = $this->query($sql);
                if ($result > 0) {
                    return $this->insert_id;
                }
            }
        }
        return 0;
    }

    //return 受影响的行数
    public function insertTableRows($table, $data)
    {
        if (is_array($data)) {
            $list = array();
            foreach ($data as $k => $v) {
                $list[] = sprintf("%s='%s'", $k, $this->dbencode($v));
            }
            $count = count($list);
            if (count($list) > 0) {
                $sql = sprintf('INSERT INTO %s SET %s', $table, implode(',', $list));
                $result = $this->query($sql);
                if ($result > 0) {
                    return $this->rows_affected;
                }
            }
        }
        return 0;
    }

    public function updateTable($table, $data, $condition)
    {
        if (is_array($data)) {
            $list = array();
            foreach ($data as $k => $v) {
                $list[] = sprintf("%s='%s'", $k, $v);
            }
            if (count($list) > 0) {
                $sql = sprintf('UPDATE %s SET %s WHERE %s', $table, implode(',', $list), $condition);
                return $this->query($sql);
            }
        }
        return false;
    }

    public function delRowByCondition($table, $condition)
    {
        $sql = sprintf("DELETE FROM %s WHERE %s", $table, $condition);
        return $this->query($sql);
    }

    public function getConditionArray($data, $appendCondition = array())
    {
        $list = array();
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                if (is_int($v)) {
                    $list[] = sprintf('%s=%d', $k, $v);
                } else {
                    $list[] = sprintf('%s=\'%s\'', $k, $v);
                }
            }
        }
        foreach ($appendCondition as $v) {
            $list[] = $v;
        }
        return $list;
    }

    public function dbencode($str, $size = 0)
    {
        if ($size > 0) {
            $str = mb_substr($str, 0, $size);
        }
        // 	checkDenyWords($str);
        return mysql_real_escape_string($str);//直接处理了
    }

    /**
     *鏇挎崲鏁版嵁琛ㄨ〃涓殑鏁版嵁
     * @param string [@sTable] 瑕佹搷浣滅殑鏁版嵁琛�    *@param mixed [@apram] 鏌ヨ鏉′欢,鏁扮粍鎴栬�瀛楃涓�    *@return int
     */
    public function replaceData($sTable, $aParam)
    {
        $sInsertSql = $this->getReplaceSql($sTable, $aParam);
        return $this->query($sInsertSql);
    }

    /**
     *鏌ヨ璁板綍鏄惁瀛樺湪
     * @param string [@sTable] 瑕佹搷浣滅殑鏁版嵁琛�    *@param mixed [@apram] 鏌ヨ鏉′欢,鏁扮粍鎴栬�瀛楃涓�    *@return mixed
     */
    public function isExist($sTable, $aParam)
    {
        $sSql = "SELECT * FROM `{$sTable}` " . $this->getWhereSql($aParam) . " limit 1";
        $rd = $this->get_row($sSql);
        return $rd ? true : false;
    }

    /**
     *鏇存柊鏁版嵁琛ㄨ褰�    *@param string [@sTable] 瑕佹搷浣滅殑鏁版嵁琛�    *@param array [@aPram] 闇�鏇存柊鐨勬暟鎹�    *@param array [@aWhere] 鏇存柊鏉′欢
     * @return int 鍙楀奖鍝嶇殑鏉℃暟
     */
    public function updateData($sTable, $aParam, $aWhere)
    {
        $sSet = $this->a2s($aParam, ',');
        if (empty($sSet)) {
            return false;
        }
        $sWhereSegment = $this->getWhereSql($aWhere);
        $sUpdateSql = "UPDATE `$sTable` set $sSet $sWhereSegment";
        return $this->query($sUpdateSql);
    }

    /**
     * @param string [$sTable] 瑕佹墽琛屽垹闄ゆ搷浣滅殑鐨勬暟鎹〃鍚嶇О
     * @pram mixed [$param] 鍒犻櫎鏉′欢,鍏宠仈鏁扮粍鎴栬�瀛楃涓�     *@return int 杩斿洖鍙楀奖鍝嶇殑鏁版嵁鏉℃暟
     */
    public function del($sTable, $param)
    {
        $delSql = "DELETE FROM `{$sTable}` " . $this->getWhereSql($param);
// 		haloDebugPrint($delSql);
        return $this->query($delSql);
    }

    /**
     *鏋勫缓鎻掑叆sql
     * @param string [$table] 鎵ц鎻掑叆鎿嶄綔鐨勮〃
     * @param array [$param] 鎻掑叆鏁版嵁
     * @return string 鎻掑叆sql
     */
    public function getInsertSql($table, $param)
    {
        $f = $values = $fields = '';
        if ($param && is_array($param)) {
            foreach ($param as $key => $item) {
                $fields .= "$f`$key`";
                $values .= "$f'$item'";
                $f = ',';
            }
            $sql = "INSERT INTO `$table` ($fields) VALUES ($values)";
            return $sql;
        }
    }

    /**
     *鏋勫缓鏇挎崲sql
     * @param string [$table] 鎵ц鏇挎崲鎿嶄綔鐨勮〃
     * @param array [$param] 鏇挎崲鏁版嵁
     * @return string 鎻掑叆sql
     */
    public function getReplaceSql($table, $param)
    {
        $f = $values = $fields = '';
        foreach ($param as $key => $item) {
            $fields .= "$f`$key`";
            $values .= "$f'$item'";
            $f = ',';
        }
        $sql = "REPLACE  INTO `$table` ($fields) VALUES ($values)";
        return $sql;
    }


    //鏋勫缓sql where閮ㄥ垎
    public function getWhereSql($param, $concat = 'AND')
    {
        if ($param) {
            if (is_array($param)) {
                if ($sTmp = $this->a2s($param, $concat)) {
                    return ' WHERE ' . $sTmp;
                }
            } else {
                if (is_string($param)) {
                    return ' WHERE ' . $param;
                }
            }
        }
        return '';
    }

    //sql鏌ヨ鏉′欢鎷兼帴
    private function a2s($param, $concat = 'AND')
    {
        $str = '';
        $f = '';
        foreach ($param as $key => $item) {
            $str .= " $f `$key` = '$item'";
            $f = $concat;
        }
        return $str;
    }


    public function getQuery($noLimitSql, $sql)
    {
        $count = $this->get_var($noLimitSql);
        $result = $this->get_results($sql, ARRAY_A);
        return ['count' => $count, 'result' => $result];
    }


    public function __destruct()
    {
        parent::close();
    }


}
