<?php
/*!
 * ucdeploy project
 *
 * Copyright 2017 xiebojie@qq.com
 * Licensed under the Apache License v2.0
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 */
class user_model extends model
{
    protected $primary_table = 'user';
    protected $primary_key = 'id';
    
    const STATUS_DISABLE=0;
    const STATUS_ENABLE =1;
    
    public static $status_list = array(
        self::STATUS_DISABLE => '已禁用',
        self::STATUS_ENABLE  => '已启用'
    );
    
    public function set_status($uid, $status)
    {
        $uid = intval($uid);
        $status = intval($status);
        $sql = "UPDATE user SET status=$status WHERE id=$uid";
        return self::$db->replace($sql);
    }
    
    public function fetch_by_username($username)
    {
        $username = addslashes($username);
        $sql = "SELECT * FROM user WHERE username='$username'";
        return self::$db->fetch($sql);
    }
}