{%extend base.inc.php%}
{%block main%}
<div class="pageheader">
    <h1 class="pagetitle">系统管理</h1>
    <ul class="hornav">
        <li><a href="/user/list">账号管理</a></li>
        <li class="current"><a href=""><?php echo empty($user)?'添加账号':'编辑账号'?></a></li>
        <li><a href="/user/syslog">操作日志</a></li>
    </ul>
</div>
<div class="contentpanel">
    <form class="form-horizontal mt20" role="form" method="post">
        <div class="form-group">
            <label class="col-sm-1 control-label">用户名</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" data-rule="required" name="username" value="{%$user['username']|default:''%}"/>
            </div>
        </div>
        <div class="form-group">
            <label  class="col-sm-1 control-label">手机号</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" data-rule="required" name="mobile" value="{%$user['mobile']|default:''%}"/>
            </div>
        </div>
        <div class="form-group">
            <label  class="col-sm-1 control-label">Email</label>
            <div class="col-sm-10">
                <input type="text" class="form-control"  data-rule="required" name="email" value="{%$user['email']|default:''%}"/>
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
{%endblock%}