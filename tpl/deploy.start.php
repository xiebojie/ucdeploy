{%extend base.inc.php%}
{%block main%}
<div class="pageheader">
    <h1 class="pagetitle">代码发布管理</h1>
    <ul class="hornav">
        <li><a href="/project/list">项目列表</a></li>
        <li><a href="/project/form">添加项目</a></li>
        <li><a href="/deploy/list">发布列表</a></li>
        <li class="current"><a href="/deploy/start">创建发布</a></li>
    </ul>
</div>
<div class="contentpanel" style="padding-bottom: 60px">
    <?php if(!empty($error)):?><div class="alert alert-danger">{%$error%}</div><?php endif;?>
    <?php if(empty($project)):?>
    <div class="alert alert-danger">你没有权限发布此项目</div>
    <?php elseif(!empty($last_deploy) && in_array($last_deploy['status'],array())):?>
    <div class="alert alert-danger">上一次发布任务还没有完成，项目空间已锁定，
        <a href="/deploy/detail?deploy_id={%$last_deploy['id']%}">查看详情</a> <br/>
        <a href="/project/unlock?project_id={%$project['id']%}">解锁</a>
    </div>
    <?php else:?>
    <form class="form-horizontal mt30 js-ajaxform" method="post" >
        <div class="form-group">
            <label  class="col-sm-1 control-label">项目</label>
            <div class="col-sm-10">{%$project['project_name']%}
                <table class="table table-striped" id="loglist"></table>
            </div>
        </div>
        <div class="form-group">
            <label  class="col-sm-1 control-label">发布备注</label>
            <div class="col-sm-10">
                <textarea type="text" class="form-control" name="remark" data-rule="required"></textarea>
            </div>
        </div>
        <div class="form-group">
            <label  class="col-sm-1 control-label">待上线文件<br/></label>
            <div class="col-sm-10">
                <?php if(!empty($list_diff)):?>
                <ul class="list-group">
                    <?php foreach ($list_diff as $_file): ?>
                        <li class="list-group-item">
                            <label>
                                <input type="checkbox" name="file[]" value="{%$_file['file']%}" checked="checked"/>
                                {%$_file['file']%}</label>
                            <span class="badge"><?php echo $_file['act'] ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <?php else:?>
                <div class="alert alert-warning">没有更新过的文件</div>
                <?php endif;?>
                <ul class="well tree">
                <li>
                    <input type="checkbox" /><label class="tree-toggle tree-parent text-primary">未修改的文件<i class="glyphicon glyphicon-chevron-down"></i></label>
                    <?php echo tree_view($list_all)?>
                </li>
                </ul>
            </div>
        </div>
        <div style="position:fixed;bottom: 10px; width: 89%">
             <label  class="col-sm-1 control-label"></label>
            <div class="well well-sm col-sm-10">
                 <button type="submit" class="btn btn-success">下一步(代码审核)</button>
             </div>
        </div>
        <input type="hidden" name="project_id" value="{%$project['id']%}"/>
    </form>
    <?php endif;?>
</div>
<style type="text/css">
    label{
        font-weight: normal;
    }
    input[type="checkbox"] {
        margin-top: 0px;
        margin-right: 0.5em;
        vertical-align: -1px;
    }
    .tree li{
        list-style: none;
    }
    .tree >ul{
        padding-left: 28px;
    }
</style>
<script src="/script/bootstrap.treeview.js"></script>
<?php
function tree_view($arr)
{
    $html = '<ul class="tree">';
    foreach ($arr as $k=>$v)
    {
        if(is_array($v))
        {
            $html.=sprintf('<li><input type="checkbox" name="file[]" value="%s" /><label class="tree-toggle tree-parent">%s/</label> %s</li>', 
                    $k, basename($k),tree_view($v));
        }else
        {
            $html.=sprintf('<li><label><input type="checkbox" name="file[]" value="%s" />%s</label></li>',$k,$v);
        }
    }
    $html.='</ul>';
    return $html;
}

?>
<script>
    $(function(){
        var dialog = bootbox.dialog({
            title: '更新代码库',
            message: '<p><img src="/style/images/loading.svg" width="16" height="16"/> 请稍后……</p>'
        });
        dialog.init(function(){
            $.post('/deploy/svnup?project_id='+$('input[name=project_id]').val(),function(r){
                var resp = $.parseJSON(r);
                if(resp.error===0){
                    $('#loglist').append('<tr><td>开发者</td><td>备注</td><td width="150" align="center">日期</td></tr>');
                    for(var i in resp.loglist)
                    {
                        $('#loglist').append('<tr><td>'+resp.loglist[i].author+
                                '</td><td>'+resp.loglist[i].msg+'</td><td>'+resp.loglist[i].date+'</td></tr>');
                    }
                    dialog.modal('hide');
                }else{
                    dialog.find('.bootbox-body').html(resp.message);
                }
               
            });
        });
    });
</script>
{%endblock%}