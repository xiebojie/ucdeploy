<?php
/*!
 * ucdeploy project
 *
 * Copyright 2017 xiebojie@qq.com
 * Licensed under the Apache License v2.0
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 */
class project_ctrl extends ctrl
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new project_model();
    }

    public function index()
    {
        $filter_rules = array(
            'id'=>'column:id|compare:equal',
            'project_name'=>'column:project_name|compare:like',
            'server'=>'column:deploy_server,regression_server|compare:like'
        );
       
        $filter_where = form_filter_parse($filter_rules, $_GET);
        $project_ids = $this->model->find_granted_project($this->user_id);
        $filter_where[]=  "id IN('".implode("','", $project_ids)."')";
        list($page, $psize) = $this->fetch_paging_param();
        list($project_list, $total) = $this->model->search_list($filter_where, ($page-1)*$psize, $psize);
        $this->assign('project_list', $project_list,'total',$total);
        $this->display('project.list.php');
    }
    
    public function form()
    {
        $project_id = isset($_REQUEST['project_id'])?$_REQUEST['project_id']:-1;
        $project = $this->model->fetch($project_id);
        if ($_SERVER['REQUEST_METHOD'] == 'POST')
        {
            $valid_fields = array(
                'project_name'=>'required',
                'repository_type'=>'required',
                'repository_url'=>'required',
                'repository_user'=>'optional',
                'repository_passwd'=>'optional',
                'deploy_path'=>'required',
                'deploy_server'=>'required',
                'regression_server'=>'required',
                'exclude_path'=>'optional'
            );
            $validator = new validator();
            list($valid_data, $valid_error) = $validator->validate($_POST, $valid_fields);
            if (empty($valid_error))
            {
                $valid_data['operator']=  $this->username;
                $valid_data['utime'] = 'timestamp';
                if (empty($project))
                {
                    $valid_data['ctime'] = 'timestamp';
                    $project_id = $this->model->insert($valid_data); 
                    $project = $this->model->fetch($project_id); 
                    $this->model->grant($project_id, array($this->user_id=>$this->username));
                }else
                {
                    $this->model->update($project_id, $valid_data);
                }
                if (!file_exists(BASEPATH . "deploy/$project_id/"))
                {
                    mkdir(BASEPATH . "deploy/$project_id", 0755, true);
                    mkdir(BASEPATH . "deploy/$project_id/code", 0755, true);
                    mkdir(BASEPATH . "deploy/$project_id/online", 0755, true);
                    mkdir(BASEPATH . "deploy/$project_id/backup", 0755, true);
                    /*$repo= get_repository($project['repository_type'], BASEPATH."deploy/$project_id/code", $project['repository_user'], 
                            $project['repository_passwd']);
                    $repo->checkout($project['repository_url']);*/
                }
                return array('error'=>0, 'message'=>'','project_id'=>$project_id);
            }
            return array('error'=>1,'message'=>  implode(',',$valid_error));
        }else
        {
            $this->assign('project', $project);
            $this->display('project.form.php');
        }       
    }
    
 
    //检查ssh信息是否加了信任关系
    public function is_ssh_valid()
    {
        $server_list = isset($_REQUEST['server'])?explode("\n",$_REQUEST['server']):array();
        $valid = array('error'=>0,'message'=>'');
        foreach ($server_list as $_server)
        {
            $ssh = ssh2_connect($_server);
            if (false === $ssh) 
            {
                $valid['message'].="can't connect to $_server\n";
            }
        }
        return $valid;
    }
    
    //修改项目授权
    public function grant()
    {
        $project_id= isset($_REQUEST['project_id'])?$_REQUEST['project_id']:'-1';
        $user_ids = isset($_REQUEST['user_ids'])?explode(',', $_REQUEST['user_ids']):array(); 
        $user_model = new user_model();
        if($_SERVER['REQUEST_METHOD'] == 'POST')
        {
            $developer_list = array();
            foreach ($user_ids as $uid)
            {
                $user = $user_model->fetch($uid);
                if(!empty($user))
                {
                    $developer_list[$uid]=$user['username'];
                }
            }
            $this->model->grant($project_id, $developer_list);
        }else
        {
            $user_list = $user_model->fetch_all();
            $developer_list = $this->model->fetch_developer($project_id);
            foreach ($user_list as &$user) 
            {
                if (isset($developer_list[$user['id']]))
                {
                    $user['checked'] = 1;
                }
            }
            return $user_list;
        }
    }
        
    public function checkout($project_id = -1)
    {
        set_time_limit(0);
        if(!isset($_SESSION['checkout']) || $_SESSION['checkout']<($_SERVER['REQUEST_TIME']-120))
        {
            $project = $this->model->fetch($project_id);
            $repo = get_repository($project['repository_type'], BASEPATH . "deploy/$project_id/code", $project['repository_user'],
                    $project['repository_passwd']);
            $repo->checkout($project['repository_url']);
            unset($_SESSION['checkout']);
        }
        return array('error'=>0,'message'=>'','redirect'=>'/project/list');
    }
    
}