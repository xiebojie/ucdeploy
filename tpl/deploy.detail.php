{%extend base.inc.php%}
{%block main%}
<div class="pageheader">
    <h1 class="pagetitle">代码发布管理</h1>
    <ul class="hornav">
        <li><a href="/project/list">项目列表</a></li>
        <li><a href="/project/form">添加项目</a></li>
        <li><a href="/deploy/list">发布列表</a></li>
        <li class="current"><a href="">发布详情</a></li>
    </ul>
</div>
<div class="contentpanel">
    <?php if(empty($deploy)):?>
    <div class="alert alert-danger">发布任务不存在</div>
    <?php else:?>
    <table class="table table-bordered">
        <tr>
            <th width="160">项目名称</th>
            <td>{%$deploy['project_name']%}</td>
        </tr>
        <tr>
            <th>发布状态</th>
            <td><?php echo deploy_model::$status_list[$deploy['status']]?></td>
        </tr>
        <tr>
            <th>发布文件</th>
            <td>{%$deploy['filelist']|nl2br|raw%}</td>
        </tr>
    </table>
    <h5 class="caption">发布日志</h5>
    <table class="table table-bordered">
        <tr class="active">
            <th width="42">id</th>
            <th>发布状态</th>
            <th>内容</th>
            <th>负责人</th>
            <th width="120">时间</th>
        </tr>
        <?php foreach ($loglist as $log):?>
        <tr>
            <td>{%$log['id']%}</td>
            <td><?php echo deploy_model::$status_list[$log['status']]?></td>
            <td>{%$log['content']|nl2br|raw%}</td>
            <td>{%$log['operator']%}</td>
            <td>{%$log['ctime']%}</td>
        </tr>
        <?php endforeach;?>
    </table>
    <?php endif;?>
</div>
{%endblock%}