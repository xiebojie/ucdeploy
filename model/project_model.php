<?php
/*
 * ucdeploy project
 *
 * Copyright 2017 xiebojie@qq.com
 * Licensed under the Apache License v2.0
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 */
class project_model extends model
{
    protected $primary_table = 'uc_project';
    protected $primary_key = 'id';
    
    public function grant($project_id, Array $developer_list)
    {
        $project_id = intval($project_id);
        self::$db->delete("DELETE FROM project_developer WHERE project_id=$project_id");
        foreach ($developer_list as $uid=>$username)
        {
            self::$db->insert("INSERT INTO project_developer SET project_id=$project_id,user_id='$uid'");
        }
        $sql = sprintf("UPDATE project SET developer='%s' WHERE id=$project_id" , addslashes(implode(',', $developer_list))) ;
        self::$db->replace($sql);
    }
    
    public function fetch_developer($project_id)
    {
        $project_id = intval($project_id);
        $sql = "SELECT * FROM uc_project_developer  WHERE project_id=$project_id";
        $list = array();
        foreach (self::$db->fetch_all($sql) as $_row)
        {
            $list[$_row['user_id']] = $_row;
        }
        return $list;
    }
    
    public function set_last_deploy_id($project_id,$deploy_id)
    {
        $project_id = intval($project_id);
        $deploy_id = intval($deploy_id);
        $sql = "UPDATE uc_project SET last_deploy_id=$deploy_id WHERE id=$project_id";
        return self::$db->replace($sql);
    }
    
    public function find_granted_project($user_id)
    {
        $user_id = intval($user_id);
        $sql = "SELECT project_id FROM uc_project_developer WHERE user_id=$user_id ";
        $project_ids = array();
        foreach (self::$db->fetch_all($sql) as $row)
        {
            $project_ids[] = $row['project_id'];
        }
        return $project_ids;
    }
    
    public function fetch_by_delveloper($project_id, $user_id)
    {
        $project_id = intval($project_id);
        $user_id = intval($user_id);
        $sql = "SELECT project.* FROM uc_project LEFT JOIN uc_project_developer ON uc_project.id = uc_project_developer.project_id WHERE uc_project.id=$project_id
                AND user_id=$user_id";
        return self::$db->fetch($sql);
    }
  
}

