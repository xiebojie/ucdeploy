<?php
/*!
 * ucdeploy project
 *
 * Copyright 2017 xiebojie@qq.com
 * Licensed under the Apache License v2.0
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 */
class user_ctrl extends ctrl
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new user_model();
    }

    public function index()
    {
         $filter_rules = array(
            'id'=>'column:id|compare:equal',
        );
        $filter_where = form_filter_parse($filter_rules, $_GET);
        list($page, $psize) = $this->fetch_paging_param();
        list($user_list, $total) = $this->model->search_list($filter_where, ($page-1)*$psize, $psize);
        $this->assign('user_list', $user_list,'total', $total);
        $this->display('user.list.php');
    }
    
    public function form($user_id=-1)
    {
        $user = $this->model->fetch($user_id);
        if($_SERVER['REQUEST_METHOD'] == 'POST')
        {
             $valid_fields = array(
                'username'=>'required',
                'mobile'=>'required',
                'email'=>'required'
            );
            $validator = new validator();
            list($valid_data, $valid_error) = $validator->validate($_POST, $valid_fields);
            if (empty($valid_error))
            {
                $valid_data['operator']=  $this->username;
                if (empty($user))
                {
                    $valid_data['ctime']= 'timestamp';
                    $valid_data['status']= user_model::STATUS_DISABLE;
                    $user_id = $this->model->insert($valid_data);
                }else
                {
                    $valid_data['utime']= 'timestamp';
                    $this->model->update($user_id, $valid_data);
                }
                return redirect('/user/list');
            }
        }else
        {
            $this->assign('user', $user);
            $this->display('user.form.php');
        }
    }

    public function disable()
    {
        $uid = isset($_REQUEST['id'])?$_REQUEST['id']:-1;
        $status = isset($_REQUEST['status'])?$_REQUEST['status']:user_model::STATUS_DISABLE;
        $user = $this->model->fetch($uid);
        if($_SERVER['REQUEST_METHOD'] == 'POST'&& !empty($user))
        {
            $this->model->set_status($uid,$status);
        }
    }
    
    public function logout()
    {
        session_destroy();
        return redirect('/');
    }
    
}