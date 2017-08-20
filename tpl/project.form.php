{%extend base.inc.php%}
{%block main%}
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
                <select name="repository_type" class="form-control" data-val="{%$project['repository_type']|default:''%}">
                    <option value="svn">svn</option>
                    <option value="git">git</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label  class="col-sm-1 control-label">项目名</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="project_name" data-rule="required" 
                    value="{%$project['project_name']|default:''%}"/>
            </div>
        </div>
        <div class="form-group">
            <label  class="col-sm-1 control-label">源码库地址</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="repository_url" data-rule="required" 
                    value="{%$project['repository_url']|default:''%}"/>
            </div>
        </div>
        <div class="form-group only-svn">
            <label  class="col-sm-1 control-label">源码库用户</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="repository_user" value="{%$project['repository_user']|default:''%}"/>
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
                    value="{%$project['deploy_path']|default:''%}"/>
            </div>
        </div> 
        <div class="form-group">
            <label  class="col-sm-1 control-label">排除文件</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="exclude_path"  placeholder="用','分开"
                    value="{%$project['exclude_path']|default:''%}"/>
            </div>
        </div>    
        <div class="form-group">
            <label  class="col-sm-1 control-label">回归机</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="regression_server" data-rule="required"
                    value="{%$project['regression_server']|default:''%}"/>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-1 control-label">部署机器</label>
            <div class="col-sm-10">
                <textarea type="text" class="form-control" cols="32" rows="6" name="deploy_server" 
                    data-rule="required">{%$project['deploy_server']|default:''%}</textarea>
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
{%endblock%}