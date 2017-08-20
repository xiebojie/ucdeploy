{%extend base.inc.php%}
{%block main%}
<div class="pageheader">
    <h1 class="pagetitle">代码发布管理</h1>
    <ul class="hornav">
        <li><a href="/project/list">项目列表</a></li>
        <li><a href="/project/form">添加项目</a></li>
        <li class="current"><a href="/deploy/list">发布列表</a></li>
    </ul>
</div>
<div class="contentpanel">
    <form class="search-form">
        <table class="search-table" style="width:788px">
            <tr>
                <th>项目</th>
                <td><select name="project_id" class="form-control">
                        <option value="">全部</option>
                        <?php foreach ($project_list as $_project):?>
                        <option value="<?php echo $_project['id']?>"><?php echo $_project['project_name']?></option>
                        <?php endforeach;?>
                    </select>
                </td>
                <th>状态</th>
                <td><select name="status" class="form-control">
                        <option value="">全部</option>
                        <?php foreach (deploy_model::$status_list as $k=>$v):?>
                        <option value="<?php echo $k?>"><?php echo $v;?></option>
                        <?php endforeach;?>
                    </select>
                </td>
                <th>发起人</th>
                <td><input type="text" class="form-control" name="operator"/></td>
            </tr>
            <tr>
                <th>发布日期</th>
                <td colspan="3">
                    <div class="input-group">
                        <input type="text" name="stime" class="datepicker form-control"/>
                        <span class="input-group-addon">至</span>
                        <input type="text" name="etime" class="datepicker form-control"/>
                    </div>
                </td>
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
            <th width="52">id</th>
            <th>项目</th>
            <th>备注</th> 
            <th width="130">状态</th>
            <th width="148">负责人</th>
            <th width="160">日期</th>
            <th width="260">操作</th>
        </tr>
        <?php foreach ($deploy_list as $_deploy): ?>
            <tr>
                <td align="center">{%$_deploy['id']%}</td>
                <td>{%$_deploy['project_name']%}</td>
                <td>{%$_deploy['remark']%}</td>
                <td align="center"><?php echo @deploy_model::$status_list[$_deploy['status']]?></td>
                <td align="center">{%$_deploy['operator']%}</td>
                <td>{%$_deploy['ctime']%}</td>
                <td align="center">
                    <?php if($_deploy['last_deploy_id']==$_deploy['id']):?>
                    <?php if($_deploy['status'] == deploy_model::STATUS_INIT):?>
                    <a href="/deploy/commit?deploy_id={%$_deploy['id']%}" class="btn btn-success js-deploy" title="发布到回归机">
                        <span class="glyphicon"></span>发布</a>
                    <?php elseif($_deploy['status'] ==deploy_model::STATUS_COMMIT ):?>
                    <a href="/deploy/release?deploy_id={%$_deploy['id']%}" class="btn btn-success js-deploy" title="发布到线上">
                        <span class="glyphicon glyphicon-flash"></span>发布</a>
                    <?php endif;?> 
                    <?php if($_deploy['status'] ==deploy_model::STATUS_COMMIT||$_deploy['status']==deploy_model::STATUS_RELEASE):?>
                    <a href="/deploy/rollback?deploy_id={%$_deploy['id']%}" class="btn btn-danger js-rollback">
                        <span class="glyphicon glyphicon-ban-circle"></span> 回滚</a>
                    <?php endif;?>
                    <?php endif;?>
                    <a href="/deploy/detail?deploy_id={%$_deploy['id']%}" class="btn btn-info"> 
                        <span class="glyphicon glyphicon-info-sign"></span> 详情</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
<script>
    $('.js-rollback').click(function(){
        var href=this.href;
        var title = this.title;
        var dialog = bootbox.dialog({
            title: title,
            message: '<p><img src="/style/images/loading.svg" width="16" height="16"/> 处理中……</p>'
        });
        dialog.init(function(){
            $.post(href,function(r){
                var resp = $.parseJSON(r);
                dialog.find('.bootbox-body').html(resp.message);
                dialog.on('hide.bs.modal', function () {
                    window.location.reload();
                });
            });
        });
        return false;
    });
    $('.js-deploy').click(function(){
        var href=this.href;
        var title = this.title;
        var dialog = bootbox.dialog({
            title: title,
            message: '<p><img src="/style/images/loading.svg" width="16" height="16"/> 处理中……</p>'
        });
        dialog.init(function(){
            $.post(href,function(r){
                var resp = $.parseJSON(r);
                dialog.find('.bootbox-body').html(resp.message);
                dialog.on('hide.bs.modal', function () {
                    window.location.reload();
                });
            });
        });
        return false;
    });
</script>
{%endblock%}  