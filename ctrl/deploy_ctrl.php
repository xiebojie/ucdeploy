<?php
/*
 * ucdeploy project
 *
 * Copyright 2017 xiebojie@qq.com
 * Licensed under the Apache License v2.0
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 */
class deploy_ctrl extends ctrl
{
    private $project_model;
    protected $model;
    public function __construct()
    {
        parent::__construct();
        $this->project_model = new project_model();
        $this->model = new deploy_model();
        ignore_user_abort(true);
        set_time_limit(0);
        $this->user_id=1;
    }

    public function index()
    {
        $filter_rules = array(
            'id' => 'column:id|compare:equal',
            'project_id' => 'column:project_id|compare:equal',
            'operator' => 'column:deploy.operator|compare:like',
            'status' => 'column:status|compare:status',
            'stime'=>'column:deploy.ctime|compare:date_start',
            'etime'=>'column:deploy.ctime|compare:date_end'
        );
        $filter_where = form_filter_parse($filter_rules, $_GET);
        $project_ids = $this->project_model->find_granted_project($this->user_id);
        $filter_where[]=  "project_id IN('".implode("','", $project_ids)."')";
        list($page, $psize) = $this->fetch_paging_param();
        list($deploy_list, $total) = $this->model->search_list($filter_where, ($page - 1) * $psize, $psize);
        list($project_list) = $this->project_model->search_list(array('uid' => $this->user_id));
        $this->assign('deploy_list', $deploy_list, 'total', $total, 'project_list', $project_list);
        $this->display('deploy.list.php');
    }

    //发起发布任务，确定任务包含的文件信息，更新代码库，创建发布任务，将当前项目的文件，拷贝到一个目录，以防回滚
    //@todo 检查project 看看上一次是否已经完成
    public function start()
    {
        $project_id= isset($_REQUEST['project_id'])?$_REQUEST['project_id']:-1;
        $project = $this->project_model->fetch_by_delveloper($project_id,  $this->user_id);
        $project_path = BASEPATH."deploy/$project_id/";
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($project))
        {
            $_POST['filelist'] = isset($_POST['file']) && is_array($_POST['file'])?implode("\n", array_unique($_POST['file'])):'';
            $valid_fields = array(
                'filelist' => 'required',
                'remark'   => 'required'
            );
            $validator = new validator();
            list($valid_data, $valid_error) = $validator->validate($_POST, $valid_fields);
            $valid_data['project_id'] = $project_id;
            $valid_data['operator'] = $this->username;
            $valid_data['utime'] = 'timestamp';
            $valid_data['status'] = deploy_model::STATUS_INIT;
            if (empty($valid_error))
            {
                $valid_data['ctime']='timestamp';
                $deploy_id = $this->model->insert($valid_data);
                $this->model->add_deploy_log($deploy_id, deploy_model::STATUS_INIT, "创建发布成功", $this->username);
                $this->project_model->set_last_deploy_id($project_id,$deploy_id);
                return array('error'=>0,'message'=>'创建发布成功','redirect'=>'/deploy/review/?deploy_id='.$deploy_id);
            }
            return array('error'=>1,'message'=>$valid_error);
        }else
        {
            $repo= get_repository($project['repository_type'], $project_path, $project['repository_user'], $project['repository_passwd']);
            $repo->update();
            $list_diff= $this->scandir_diff("{$project_path}code/", "{$project_path}online/");
            $list_all = scandir_tree("{$project_path}code/","{$project_path}code/");
            $last_deploy = $this->model->fetch($project['last_deploy_id']);
            $this->assign('project', $project, 'list_diff', $list_diff,'list_all', $list_all,'last_deploy',$last_deploy);
            $this->display('deploy.start.php');
        }
    }

    //代码审核，
    public function review()
    {
        $deploy_id = isset($_GET['deploy_id'])?$_GET['deploy_id']:-1;
        $deploy = $this->model->fetch($deploy_id);
        $project = $this->project_model->fetch_by_delveloper($deploy['project_id'], $this->user_id);
        if(empty($project))
        {
            return show_err("你没有权限查看此发布");
        }
        if (!empty($deploy))
        {
            $this->assign('filelist', explode("\n", $deploy['filelist']));
        }
        $this->assign('deploy', $deploy,'deploy_id', $deploy_id);
        $this->display('deploy.review.php');
    }

    //获得当前代码库和线上版本的文件差异
    public function diff()
    {
        $deploy_id = isset($_GET['deploy_id'])?$_GET['deploy_id']:-1;
        $deploy = $this->model->fetch($deploy_id);
        $project = $this->project_model->fetch_by_delveloper($deploy['project_id'], $this->user_id);
        if(!empty($deploy) && !empty($project))
        {
            $fname = isset($_GET['fname']) ? $_GET['fname'] : '';
            $project_path = BASEPATH."deploy/{$deploy['project_id']}/";
            $online_file =$project_path.'online/'.$fname;
            $code_file = $project_path.'code/'.$fname;
         
            $before = file_exists($online_file) ? file_get_contents($online_file) : '';
            $after = file_exists($code_file) ? file_get_contents($code_file) : '';
            if (mb_detect_encoding($after, "UTF-8, ISO-8859-1, GBK") != "UTF-8")
            {
                $before = mb_convert_encoding($before, 'utf-8','gbk');
                $after = mb_convert_encoding($after, 'utf-8','gbk');
            }
            return array('before' => $before, 'after' =>  $after); 
        }
        return array('before' => '', 'after' => '');
    }

    //发布到回归机,将当前代码打包，同时将版本库里的代码拷贝一份到线上目录
    public function commit()
    {
        $deploy_id = isset($_GET['deploy_id'])?$_GET['deploy_id']:-1;
        $deploy = $this->model->fetch($deploy_id);
        $project = $this->project_model->fetch_by_delveloper($deploy['project_id'], $this->user_id);
        if (!empty($deploy) && $deploy['status']==deploy_model::STATUS_INIT &&!empty($project))
        {
            $project = $this->project_model->fetch($deploy['project_id']);
            $project_path = BASEPATH."deploy/{$deploy['project_id']}/";
            $backup_file = date('Y-m-d_') . $deploy_id . '.zip';
            zipper::zipdir($project_path . 'online/', $project_path . 'backup/' . $backup_file);
            $this->model->set_last_backup_file($deploy_id, $backup_file);
            $filelist =  explode("\n", $deploy['filelist']);
            //先创建好目录结构
            $error =array() ;
            foreach ($filelist as $_file) 
            {
                if(is_dir("{$project_path}code/$_file")&&!file_exists("{$project_path}online/$_file"))
                {
                    if(!mkdir("{$project_path}online/$_file", 0755, true))
                    {
                        $error[]="can't makdir :$_file";
                    }
                }elseif(!file_exists ("{$project_path}code/$_file")&& file_exists("{$project_path}online/$_file"))
                {
                    if(unlink($project_path . 'online/' . $_file))
                    {
                        $error[]= "delete file failed $_file";
                    }
                }
            }
            //再拷贝文件
            foreach ($filelist as $_file) 
            {
                if(is_file("{$project_path}code/$_file"))
                {
                    if(!copy("{$project_path}code/$_file", "{$project_path}online/$_file"))
                    {
                        $error[]= "copy file failed:$_file";
                    }
                }else if(!file_exists ("{$project_path}code/$_file")&& is_dir("{$project_path}online/$_file"))
                {
                    shell_exec("rm -rf {$project_path}online/$_file");
                }
            }
            if(empty($error))
            {
                $error = $this->rsync_deploy($project, array($project['regression_server']));
                
            }
            if(empty($error))
            { 
                $this->model->add_deploy_log($deploy_id, deploy_model::STATUS_COMMIT,  "回归机发布成功", $this->username);
                $this->model->set_status($deploy_id, deploy_model::STATUS_COMMIT);
                return array('error'=>0, 'message'=>'回归机发布成功');
            }else
            {
                $this->model->add_deploy_log($deploy_id, deploy_model::STATUS_COMMIT,  "回归机发布失败\n".  implode("\n", $error), $this->username);
                return array('error'=>1,'message'=>  implode("\n", $error));
            }
        }else
        {
            return array('error'=>1, 'message'=>'发布失败<br/>发布任务状态为：'.deploy_model::$status_list[$deploy['status']]);
        }
    }

    //发布到线上机器
    public function release()
    {
        $deploy_id = isset($_REQUEST['deploy_id'])?$_REQUEST['deploy_id']:-1;
        $deploy = $this->model->fetch($deploy_id);
        $project = $this->project_model->fetch_by_delveloper($deploy['project_id'], $this->user_id);
        if(!empty($deploy)&&!empty($project)&&in_array($deploy['status'],array(deploy_model::STATUS_COMMIT)))
        {
            $server_list = explode("\n", $project['deploy_server']);
            $error=$this->rsync_deploy($project, $server_list);
            if(empty($error))
            {
                $this->model->add_deploy_log($deploy_id, deploy_model::STATUS_RELEASE, '线上发布成功', $this->username);
                $this->model->set_status($deploy_id, deploy_model::STATUS_RELEASE);
                return array('error'=>0, 'message'=>'线上发布成功');
            }else
            {
                $this->model->add_deploy_log($deploy_id,deploy_model::STATUS_RELEASE, "发布失败\n".  implode("\n", $error), $this->username);
                return array('error'=>1, 'message'=>'发布失败<br/>'.  implode("<br/>", $error));
            }
        }else
        {
            return array('error' => 1, 'message' => '发布任务不存在');
        }
    }

    //发布任务回滚，根据状态确定是只从回归机回滚还是全部回滚，将上一个成功的任务目录同步到当前目录，然后执行代码同步
    //@todo 考虑回滚失败后是否可以再次回滚
    public function rollback()
    {
        $deploy_id = isset($_REQUEST['deploy_id'])?$_REQUEST['deploy_id']:-1;
        $deploy = $this->model->fetch($deploy_id);
        $project = $this->project_model->fetch_by_delveloper($deploy['project_id'], $this->user_id);
        if (!empty($deploy) &&!empty($project)&& in_array($deploy['status'], array(deploy_model::STATUS_COMMIT, deploy_model::STATUS_RELEASE)))
        {
            $error = array();
            $project_path = BASEPATH."deploy/{$deploy['project_id']}/";
            $backup_file = trim("{$project_path}backup/{$deploy['last_backup_file']}");
            if (file_exists($backup_file))
            {
                shell_exec("rm -rf {$project_path}online/*");
                if (!zipper::extract($backup_file, "{$project_path}"))
                {
                    $error[] = "解压备份文件失败:{$deploy['last_backup_file']}";
                }
            } else
            {
                $error[] = "备份文件不存在:$backup_file";
            }
            if (empty($error))
            {
                $server_list = array($project['regression_server']);
                if ($deploy['status'] == deploy_model::STATUS_RELEASE)
                {
                    array_merge($server_list, explode("\n", $project['deploy_server']));
                }
                $error=$this->rsync_deploy($project, $server_list);
            }
            if(empty($error))
            {
                $this->model->add_deploy_log($deploy_id, deploy_model::STATUS_ROLLBACK,  '回滚成功',  $this->username);
                $this->model->set_status($deploy_id, deploy_model::STATUS_ROLLBACK);
                
                return array('error'=>0,'message'=>'回滚成功');
            } else
            {
                $this->model->add_deploy_log($deploy_id, deploy_model::STATUS_ROLLBACK,  implode("\n", $error),  $this->username);
                return array('error'=>1,'message'=>'回滚失败：<br/>'.implode("<br/>", $error));
            }
        }else
        {
            return array('error'=>1, 'message'=>"不能回滚 任务状态：".deploy_model::$status_list[$deploy['status']]);
        }
    }
    
    private function rsync_deploy($project,$server_list)
    {
        static $rsync = null;
        if (is_null($rsync))
        {
            $rsync = new shell\rsync('search', '/home/search/.ssh/id_rsa.pub');
            $exclude = explode(',', $project['exclude_path']);
            $exclude[] = '.svn';
            $rsync->set_exclude($exclude);
            $rsync->set_delete_from_target(true);
        }
        $error=array();
        foreach ($server_list as $_server)
        {
            list($exit_code,,$stderr)=$rsync->sync($_server, BASEPATH. "deploy/{$project['id']}/online/", $project['deploy_path']);
            if($exit_code !=0||!empty($stderr))
            {
                $error[]= "rsync to $_server failed with error:$stderr";
            }
        }
        return $error;
    }

    public function detail()
    {
        $deploy_id = isset($_REQUEST['deploy_id'])?$_REQUEST['deploy_id']:'-1';
        $deploy = $this->model->fetch($deploy_id);
        $project = $this->project_model->fetch_by_delveloper($deploy['project_id'], $this->user_id);
        if(empty($project))
        {
            return show_err("你没有权限查看此发布详情");
        }
        $loglist = $this->model->fetch_log_list($deploy_id);
        $this->assign('deploy', $deploy,'loglist', $loglist);
        $this->display('deploy.detail.php');
    }
    
    public function svnup()
    {
        set_time_limit(0);
        ignore_user_abort(true);
        $project_id= isset($_REQUEST['project_id'])?intval($_REQUEST['project_id']):-1;
        $project = $this->project_model->fetch($project_id);
        if (!empty($project))
        {
            $project_path = BASEPATH."deploy/{$project_id}/code/";
            $loglist = array();
            if (!empty($project))
            { 
                $repo= get_repository($project['repository_type'], $project_path, $project['repository_user'], $project['repository_passwd']);
                try
                {
                    $repo->update();
                    $loglist = $repo->get_log(5);
                }catch (Exception $ex)
                {
                    $error= $ex->getMessage();
                }
            }
        }
        if(empty($error))
        {
            return array('error'=>0,'loglist'=>$loglist,'message'=>'');
        }else
        {
            return array('error'=>1,'loglist'=>$loglist,'message'=>$error);
        }
    }

    private function scandir_diff($code_path, $online_path)
    {
        $list_diff = array();
        foreach (rscandir($code_path)as $_file) 
        {
            if($_file=='.git'||$_file=='.svn')
            {
                continue;
            }
            $online_file = $online_path . '/' . $_file;
            $code_file = $code_path . '/' . $_file;
            if (!file_exists($online_file))
            {
                $list_diff[] = array('file' => $_file, 'act' => 'new');
            }else if (md5_file($code_file) != md5_file($online_file))
            {
                $list_diff[] = array('file' => $_file, 'act' => 'update');
            }
        }

        foreach (rscandir($online_path)as $_file) 
        {
            if (!file_exists($code_path . '/' . $_file))
            {
                $list_diff[] = array('file' => $_file, 'act' => 'delete');
            }
        }
        return $list_diff;
    }
    
}