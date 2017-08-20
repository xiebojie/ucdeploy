{%extend base.inc.php%}
{%block main%}
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
        <input type="hidden" name="psize" value="{%$psize%}"/>
        <input type="hidden" name="page" value="{%$page%}"/>
        <input type="hidden" id="total_page" value="{%$total/$psize|ceil%}"/>
    </form>
    
    <div class="js-pager pull-right"><span class="total">总数：{%$total|number_format%}</span></div>
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
            <td>{%$_project['id']%}</td>
            <td>{%$_project['project_name']%}</td>
            <td>{%$_project['regression_server']%}</td>
            <td>{%$_project['deploy_server']|nl2br|raw%}</td>
            <td>{%$_project['deploy_path']%}</td>
            <td>{%$_project['repository_url']|%}</td>
            <td>{%$_project['developer']|default:''%}</td>
            <td align="center">
                <a href="/deploy/start?project_id={%$_project['id']%}" class="btn btn-success"><span class="glyphicon glyphicon-flash"></span> 发布</a>
                <a href="/project/grant?project_id={%$_project['id']%}" class="btn btn-warning js-grant"><span class="glyphicon glyphicon-gift"></span> 授权</a>
                <a href="/project/form?project_id={%$_project['id']%}" class="btn btn-info"><span class="glyphicon glyphicon-edit"></span> 编辑</a>
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
{%endblock%}