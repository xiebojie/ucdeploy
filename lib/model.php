<?php
/*!
 * ucdeploy project
 *
 * Copyright 2017 xiebojie@qq.com
 * Licensed under the Apache License v2.0
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 */
class model
{
    public static $db;
    protected $primary_table;
    protected $primary_key = 'id';

    public function fetch($primary_id)
    {
        $sql = sprintf("SELECT * FROM %s ", $this->primary_table);
        if (is_numeric($primary_id))
        {
            $primary_id = intval($primary_id);
            $sql .= sprintf(" WHERE %s=$primary_id", $this->primary_key);
        }else if (!empty($primary_id))
        {
            $sql .=' WHERE ' . self::sql_fields($primary_id);
        }
        return self::$db->fetch($sql);
    }

    public function search_list($filter_where, $offset = 0, $limit_size = 20)
    {
        $offset = abs($offset);
        $limit_size = abs($limit_size);
        $sql = sprintf("SELECT SQL_CALC_FOUND_ROWS * FROM %s ", $this->primary_table);
        if (!empty($filter_where))
        {
            $sql .=' WHERE ' . implode(' AND ', $filter_where);
        }
        $sql .=sprintf(" ORDER BY %s DESC ", $this->primary_key);
        if ($limit_size > 0)
        {
            $sql.=" LIMIT $offset,$limit_size";
        }
        $row_list = self::$db->fetch_all($sql);
        $count = self::$db->fetch_col('SELECT FOUND_ROWS()');
        return array($row_list, $count);
    }

    public function fetch_all($filter_where = array(), $offset = 0, $limit_size = 20)
    {
        $offset = abs($offset);
        $limit_size = abs($limit_size);
        $sql = sprintf("SELECT * FROM %s ", $this->primary_table);
        if (!empty($filter_where))
        {
            $sql .=" WHERE " . self::sql_fields($filter_where);
        }
        $sql .=" ORDER BY id DESC ";
        if ($limit_size > 0)
        {
            $sql.=" LIMIT $offset, $limit_size";
        }
        return self::$db->fetch_all($sql);
    }

    public function insert($valid_fields)
    {
        $fields_str = self::sql_fields($valid_fields);
        $sql = sprintf("INSERT INTO %s SET %s", $this->primary_table, $fields_str);
        return self::$db->insert($sql);
    }

    public function update($primary_id, $valid_fields, $where = array())
    {
        $primary_id = intval($primary_id);
        $fields_str = self::sql_fields($valid_fields, ',');
        $sql = sprintf("UPDATE %s SET %s WHERE %s=$primary_id", $this->primary_table, $fields_str, $this->primary_key);
        if (!empty($where))
        {
            $sql .= ' AND ' . self::sql_fields($where, ' AND ');
        }
        return self::$db->replace($sql);
    }

    public function delete($primary_id)
    {
        $sql = sprintf("DELETE FROM %s ", $this->primary_table);
        if (is_numeric($primary_id))
        {
            $primary_id = intval($primary_id);
            $sql .= sprintf(" WHERE %s=$primary_id", $this->primary_key);
        }else if (!empty($primary_id))
        {
            $sql .=' AND ' . self::sql_fields($primary_id);
        }
        return self::$db->delete($sql);
    }

    public static function sql_fields($field_list)
    {
        $arr = array();
        foreach ($field_list as $k => $v) 
        {
            if (is_numeric($k))
            {
                $arr[] = $v;
            }elseif ($v === 'timestamp')
            {
                $arr[] = sprintf("%s=%s", $k, 'NOW()');
            } else
            {
                $arr[] = sprintf("%s='%s'", $k, addslashes($v));
            }
        }
        return implode(',', $arr);
    }
}
model::$db = get_mydb();