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
    <h1 class="pagetitle">用户管理</h1>
    <ul class="hornav">
        <li><a href="/user/list">用户列表</a></li>
        <li class="current"><a href=""><?php echo empty($user)?'添加用户':'编辑用户'?></a></li>
    </ul>
</div>
<div class="contentpanel">
    <form class="form-horizontal mt20" role="form" method="post">
        <div class="form-group">
            <label class="col-sm-1 control-label">用户名</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" data-rule="required" name="username" value="<?php echo htmlspecialchars(empty($user['username'])?'':$user['username']);?>"/>
            </div>
        </div>
        <div class="form-group">
            <label  class="col-sm-1 control-label">手机号</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" data-rule="required" name="mobile" value="<?php echo htmlspecialchars(empty($user['mobile'])?'':$user['mobile']);?>"/>
            </div>
        </div>
        <div class="form-group">
            <label  class="col-sm-1 control-label">Email</label>
            <div class="col-sm-10">
                <input type="text" class="form-control"  data-rule="required" name="email" value="<?php echo htmlspecialchars(empty($user['email'])?'':$user['email']);?>"/>
            </div>
        </div>
        <div class="form-group">
            <label  class="col-sm-1 control-label"></label>
            <div class="col-sm-10">
                <button type="submit" class="btn btn-success">提 交</button>
            </div>
        </div>
    </form>
</div>
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