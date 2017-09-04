<?php
/*
* ucdeploy project
*
* Copyright 2017 xiebojie@qq.com
* Licensed under the Apache License v2.0
* http://www.apache.org/licenses/LICENSE-2.0
*
*/
class deploy_model extends model
{
    protected $primary_table = 'deploy';
    protected $primary_key   = 'id';
    
    const STATUS_INIT     = 0;
    const STATUS_COMMIT   = 1;
    const STATUS_RELEASE  = 2;
    const STATUS_ROLLBACK = 101;
    const STATUS_UNLOCK   = 102;
    public static $status_list = array(
        self::STATUS_INIT    =>'创建发布',
        self::STATUS_COMMIT  =>'发布到回归机',
        self::STATUS_RELEASE =>'发布到线上',
        self::STATUS_ROLLBACK=>'代码已回滚',
        self::STATUS_UNLOCK  =>'任务解锁'
    );
    
    public function fetch($deploy_id, $filter_where=[])
    {
        $deploy_id = intval($deploy_id);
        $sql = "SELECT deploy.*,project.project_name FROM deploy LEFT JOIN project ON deploy.project_id=project.id WHERE deploy.id=$deploy_id";
        return self::$db->fetch($sql);
    }
    
    public function search_list($filter_where, $offset = 0, $limit_size = 20)
    {
        $offset = abs($offset);
        $limit_size = abs($limit_size);
        $sql = "SELECT SQL_CALC_FOUND_ROWS deploy.*,project.project_name,project.last_deploy_id FROM deploy LEFT JOIN project ON deploy.project_id=project.id";
        if(!empty($filter_where))
        {
            $sql .=' WHERE '.  implode(' AND ', $filter_where);
        }
        $sql .=sprintf(" ORDER BY %s DESC ",  $this->primary_key);
        if($limit_size>0)
        {
            $sql.=" LIMIT $offset,$limit_size";
        }
        $row_list = self::$db->fetch_all($sql);
        $count = self::$db->fetch_col('SELECT FOUND_ROWS()');
        return array($row_list,$count);
    }
    
    public function set_status($deploy_id, $status)
    {
        $deploy_id = intval($deploy_id);
        $status = intval($status);
        $sql = "UPDATE deploy SET status=$status,utime=NOW() WHERE id=$deploy_id";
        return self::$db->replace($sql);
    }

    public function fetch_log_list($deploy_id)
    {
        $deploy_id = intval($deploy_id);
        $sql = "SELECT * FROM deploy_log WHERE deploy_id=$deploy_id ORDER BY id DESC";
        return self::$db->fetch_all($sql);
    }
    
    public function add_deploy_log($deploy_id, $status, $content, $operator)
    {
        $deploy_id = intval($deploy_id);
        $status = intval($status);
        $content= addslashes($content);
        $operator = addslashes($operator);
        $sql = "INSERT INTO deploy_log SET deploy_id=$deploy_id,status=$status, content='$content',operator='$operator',ctime=NOW()";
        return self::$db->insert($sql);
    }
    
    public function set_last_backup_file($deploy_id, $zipfile)
    {
        $deploy_id = intval($deploy_id);
        $zipfile = addslashes($zipfile);
        $sql = "UPDATE deploy SET last_backup_file='$zipfile' WHERE id=$deploy_id";
        return self::$db->replace($sql);
    }
}