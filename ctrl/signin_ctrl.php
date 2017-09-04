<?php 
/*
 * ucdeploy project
 *
 * Copyright 2017 xiebojie@qq.com
 * Licensed under the Apache License v2.0
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 */
class singin_ctrl extends ctrl
{
    private $model;
    public function __construct()
    {
        parent::__construct();
        $this->model = new user_model();
    }
    public function index()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST')
        {
            $mobile = isset($_POST['mobile']) ? $_POST['mobile'] : '';
            $passwd = isset($_POST['passwd']) ? $_POST['passwd'] : '';
            $member = $this->model->fetch_by_mobile($mobile);
            if (empty($member) || member_model::passwd_hash($passwd) != $member['passwd'])
            {
                return array('error' => 1, 'message' => '帐号密码错误');
            } else
            {
                $_SESSION['admin'] = array(
                    'id' => $member['id'],
                    'account' => $member['account']
                );
                return array('error' => 0, 'message' => '登录成功');
            }
        } else
        {
            if(!empty($_SESSION['member']))
            {
                redirect('/');
            }
            $this->display('admin/signin.php');
        }
    }
}