{%extend base.inc.php%}
{%block main%}
<link rel="stylesheet" href="/style/codediff.css"/>
<div class="pageheader">
    <h1 class="pagetitle">代码发布管理</h1>
    <ul class="hornav">
        <li><a href="/project/list">项目列表</a></li>
        <li><a href="/project/form">添加项目</a></li>
        <li><a href="/deploy/list">发布列表</a></li>
        <li class="current"><a href="/deploy/">代码review</a></li>
    </ul>
</div>
<div class="contentpanel" style="position: relative;overflow: auto;padding-bottom: 78px">
    <?php if(empty($deploy)):?>
    <div class="alert alert-danger">发布任务不存在</div>
    <?php else:?>
    <div class="col-md-2">
        <ul class="list-group diff-list">
            <?php foreach ($filelist as $_file):?>
            <?php if(!preg_match('/\/$/', $_file)):?>
            <li class="list-group-item"><a href="/deploy/diff?deploy_id=<?php echo $deploy_id?>&fname=<?php echo $_file?>">{%$_file%}</a></li>
            <?php endif;?>
            <?php endforeach;?>
        </ul>
    </div>
    <div class="col-md-10" id="diffview"></div>
    <div class="well well-sm text-right" style="position:fixed;bottom: 10px; width: 89%">
        <a href="/deploy/commit?deploy_id={%$deploy['id']%}" class="btn btn-primary" id="js-deploy">下一步（回归机发布)</a>
    </div>
    <?php endif;?>
</div>
<script src="/script/highlight.js"></script>
<script src="/script/difflib.js"></script>
<script src="/script/codediff.js"></script>
<script>
$('.diff-list a').click(function(){
    $.get(this.href,function(r){
        var resp = $.parseJSON(r);
        $('#diffview').html(codediff.buildView(resp.before, resp.after));
    });
    return false;
});
$('#js-deploy').click(function(){
    var href=this.href;
    var dialog = bootbox.dialog({
        title: '发布到回归机',
        message: '<p><img src="/style/images/loading.svg" width="16" height="16"/> 请稍后……</p>'
    }); 
    dialog.init(function(){
        $.post(href,function(r){
            var resp = $.parseJSON(r);
            if(resp.error===0){
                dialog.find('.bootbox-body').html("发布成功");
                window.setTimeout(function(){
                    window.location.href='/deploy/list';
                },5000);
            }else{
                dialog.find('.bootbox-body').html(resp.message);
            }
        });
    });
    return false;
});
</script>
{%endblock%}