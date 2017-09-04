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
        <li class="current"><a href="/project/list">项目列表</a></li>
        <li><a href="/project/form">添加项目</a></li>
        <li><a href="/deploy/list">发布列表</a></li>
    </ul>
</div>
<div class="contentpanel">
    <form class="search-form">
        <table class="search-table" style="width:550px">
            <tr>
                <th>id</th>
                <td><input type="text" class="form-control" name="id"/></td>
                <th>项目名</th>
                <td><input type="text" class="form-control" name="project_name"/></td>
                <th>部署机器</th>
                <td><input type="text" class="form-control" name="server"/></td>
            </tr>
            <tr>
                <th></th>
                <td><button type="submit" class="btn btn-primary">查 询</button></td>
            </tr>
        </table>
        <input type="hidden" name="psize" value="<?php echo htmlspecialchars($psize);?>"/>
        <input type="hidden" name="page" value="<?php echo htmlspecialchars($page);?>"/>
        <input type="hidden" id="total_page" value="<?php echo htmlspecialchars(ceil($total/$psize));?>"/>
    </form>
    
    <div class="js-pager pull-right"><span class="total">总数：<?php echo htmlspecialchars(number_format($total));?></span></div>
    <table class="table table-bordered table-striped">
        <tr>
            <th>id</th>
            <th>项目名</th>
            <th>回归机</th>
            <th>部署机</th>
            <th>部署路径</th>
            <th>代码库</th>
            <th>开发者</th>
            <th width="260">操作</th>
        </tr>
        <?php foreach ($project_list as $_project):?>
        <tr>
            <td><?php echo htmlspecialchars($_project['id']);?></td>
            <td><?php echo htmlspecialchars($_project['project_name']);?></td>
            <td><?php echo htmlspecialchars($_project['regression_server']);?></td>
            <td><?php echo nl2br($_project['deploy_server']);?></td>
            <td><?php echo htmlspecialchars($_project['deploy_path']);?></td>
            <td><?php echo htmlspecialchars(($_project['repository_url']));?></td>
            <td><?php echo htmlspecialchars(empty($_project['developer'])?'':$_project['developer']);?></td>
            <td align="center">
                <a href="/deploy/start?project_id=<?php echo htmlspecialchars($_project['id']);?>" class="btn btn-success"><span class="glyphicon glyphicon-flash"></span> 发布</a>
                <a href="/project/grant?project_id=<?php echo htmlspecialchars($_project['id']);?>" class="btn btn-warning js-grant"><span class="glyphicon glyphicon-gift"></span> 授权</a>
                <a href="/project/form?project_id=<?php echo htmlspecialchars($_project['id']);?>" class="btn btn-info"><span class="glyphicon glyphicon-edit"></span> 编辑</a>
            </td>
        </tr>
        <?php endforeach;?>
    </table>
</div>
<script>
    $('.js-grant').click(function(){
        var href=this.href;
        $.get(this.href,function(r){
            var resp = $.parseJSON(r);
            var inputOptions=[];
            for(i in resp)
            {
                inputOptions.push({'text':resp[i].username,'value':resp[i].id,'checked':true});
            }
            bootbox.prompt({
                title: "选择授权的账号",
                inputType: 'checkbox',
                inputOptions: inputOptions,
                callback: function (ids) {
                    $.post(href+='&user_ids='+ids,function(){
                        window.location.reload();
                    });
                }
            });
        });
        return false;
    });
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