<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <link rel="icon" href="/style/images//favicon.ico" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
        <title><?php echo htmlspecialchars(empty($title)?'ucdeploy':$title);?></title>
        <link href="/style/bootstrap.min.css" rel="stylesheet"/>
        <link href="/style/bootstrap.datepicker.css" rel="stylesheet"/>
        <link href="/style/admin.base.css" rel="stylesheet"/>
        <script src="/script/jquery.min.js"></script>
    </head>
    <body>
        <div class="navbar" style="background:#428bca;border-radius: 0px;margin: 0px">
            <div class="navbar-brand" style="font-size:28px;color:#fff">ucdeploy</div>
              <div class="pull-right whoami">
                   <a class="dropdown-toggle" data-toggle="dropdown">
                    <span class="glyphicon glyphicon-user"></span><?php echo htmlspecialchars(empty($username)?'':$username);?>
                </a>
                <ul class="dropdown-menu">
                    <li><a href="/user/logout">退出</a></li>
                </ul>
              </div>
        </div>
        <div>
            <div class="leftpanel">
                <ul class="nav" >
                    <li style="border-top: 0.5px solid #e7e7e7">
                        <a href="/"><i class="glyphicon glyphicon-home"></i>首页</a>
                    </li>
                    <li class="parent"><a href="/project/list">项目列表</a></li> 
                    <li class="parent"><a href="/project/form">添加项目</a></li>
                    <li class="parent"><a href="/deploy/list">发布列表</a></li>
                    <li class="parent"><a href="/user/list">用户列表</a></li>
                    <li class="parent"><a href="/user/form">添加用户</a></li>
                </ul>
            </div>
            <div class="mainpanel">
                <div class="pageheader">
    <h1 class="pagetitle">代码发布管理</h1>
    <ul class="hornav">
        <li><a href="/project/list">项目列表</a></li>
        <li class="current"><a href="/project/form"><?php echo empty($project)?'添加项目':'编辑项目'?></a></li>
        <li><a href="/deploy/list">发布列表</a></li>
    </ul>
</div>
<div class="contentpanel">
    <div class="alert alert-warning" style="width: 880px">
        发布账号统一使用search，在发布前请确认dev3v.white.corp.qihoo.net到发布机的信任关系
    </div>
    <form class="form-horizontal mt30" method="post">
        <div class="form-group">
            <label  class="col-sm-1 control-label">源码工具</label>
            <div class="col-sm-10">
                <select name="repository_type" class="form-control" data-val="<?php echo htmlspecialchars(empty($project['repository_type'])?'':$project['repository_type']);?>">
                    <option value="svn">svn</option>
                    <option value="git">git</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label  class="col-sm-1 control-label">项目名</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="project_name" data-rule="required" 
                    value="<?php echo htmlspecialchars(empty($project['project_name'])?'':$project['project_name']);?>"/>
            </div>
        </div>
        <div class="form-group">
            <label  class="col-sm-1 control-label">源码库地址</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="repository_url" data-rule="required" 
                    value="<?php echo htmlspecialchars(empty($project['repository_url'])?'':$project['repository_url']);?>"/>
            </div>
        </div>
        <div class="form-group only-svn">
            <label  class="col-sm-1 control-label">源码库用户</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="repository_user" value="<?php echo htmlspecialchars(empty($project['repository_user'])?'':$project['repository_user']);?>"/>
            </div>
        </div>
        <div class="form-group only-svn">
            <label  class="col-sm-1 control-label">源码库密码</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="repository_passwd" />
            </div>
        </div>
        <div class="form-group">
            <label  class="col-sm-1 control-label">部署路径</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="deploy_path" data-rule="required"
                    value="<?php echo htmlspecialchars(empty($project['deploy_path'])?'':$project['deploy_path']);?>"/>
            </div>
        </div> 
        <div class="form-group">
            <label  class="col-sm-1 control-label">排除文件</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="exclude_path"  placeholder="用','分开"
                    value="<?php echo htmlspecialchars(empty($project['exclude_path'])?'':$project['exclude_path']);?>"/>
            </div>
        </div>    
        <div class="form-group">
            <label  class="col-sm-1 control-label">回归机</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="regression_server" data-rule="required"
                    value="<?php echo htmlspecialchars(empty($project['regression_server'])?'':$project['regression_server']);?>"/>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-1 control-label">部署机器</label>
            <div class="col-sm-10">
                <textarea type="text" class="form-control" cols="32" rows="6" name="deploy_server" 
                    data-rule="required"><?php echo htmlspecialchars(empty($project['deploy_server'])?'':$project['deploy_server']);?></textarea>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-1 control-label"></label>
            <div class="col-sm-10">
                <button type="submit" class="btn btn-success"><?php echo empty($project)?'添加项目':'编辑项目'?></button>
            </div>
        </div>
    </form>
</div>
<script>
    $(function(){
        $('form').validator(function(form){
            var dialog = bootbox.dialog({
                title: '请求处理中',
                message: '<p class="text-muted"><img src="/style/images/loading.svg" width="16" height="16"/>  正在创建项目，拉取代码耗时较长……</p>'
            });
            dialog.init(function()
            {
                $(form).ajaxSubmit({'success':function(r){
                    var resp = $.parseJSON(r);
                    if(resp.error===0)
                    {
                        checkout(resp.project_id);
                    }else
                    {
                        dialog.find('.bootbox-body').html(resp.message);
                    }
                }});
            });
        });
        $('select[name=repository_type]').change(function(){
            if(this.value==='git'){
                $('.only-svn').hide();
            }else{
                $('.only-svn').show();
            }
        }).trigger('change');
    });

    function checkout(project_id)
    {
        var dialog = bootbox.dialog({
            title: '拉取代码',
            message: '<p class="text-muted"><img src="/style/images/loading.svg" width="16" height="16"/>拉取代码耗时较长……</p>'
        });
        dialog.init(function()
        {
            $.post('/project/checkout/'+project_id,function(){
                dialog.modal('hide');
            });
        });
    }
</script>
            </div>
        </div>
        <script src="/script/bootstrap.js"></script>
        <script src="/script/jquery.pagination.js"></script>
        <script src="/script/jquery.validator.js"></script>
        <script src="/script/jquery.validator.zh.js"></script>
        <script src="/script/bootstrap-datetimepicker.js"></script>
        <script src="/script/bootstrap-datetimepicker.zh.js"></script>
        <script src="/script/jquery.form.js"></script>
        <script src="/script/bootbox.js"></script>
        <script src="/script/admin.base.js"></script>
        <script>
            var path = window.location.pathname.replace(/\/(\d+|index)/,'');
            $('.leftpanel .nav li a').each(function() {
                if (this.href.indexOf(path) !==-1) 
                {
                    $(this).parent('li').addClass('active');
                    $(this).parent('li').parent().parent().addClass('active');
                    return false;
                }
            });
            $('.parent >a').click(function(){
                $('.active').removeClass('active');
                $(this).parent('li').addClass('active');
            });
        </script>
    </body>
</html>