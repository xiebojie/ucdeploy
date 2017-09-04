<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>ucdeploy</title>
        <link href="/style/bootstrap.min.css" rel="stylesheet"/>
        <link href="/style/ucbase.css" rel="stylesheet" />
        <script src="/script/jquery.min.js"></script>
    </head>
    <body style="background: #f7fafc;vertical-align: middle;">
        <div class="wraper center-block" style="width:280px">
            <h1 class="text-primary text-center">uddeploy</h1>
            <form method="post">
                <div class="form-alert"></div>
                <div class="form-group">
                    <input type="text" name="mobile" class="form-control" placeholder="手机号"
                           data-rule="手机号:required;mobile" data-target=".form-alert"/>
                </div>
                <div class="form-group">
                    <input type="password" name="passwd" class="form-control" 
                           placeholder="密码" 
                           data-rule="密码:required" data-target=".form-alert"/>
                </div>
                <!--<div class="form-group">
                    <div class="input-group">
                    <input type="text" name="img_captcha" class="form-control" 
                           placeholder="验证码" data-rule="验证码:required" 
                           data-target=".form-alert"/>
                    <span class="input-group-btn">
                    <img onclick="this.src = '/captcha/img?r=' + Math.random()" 
                         src="/captcha/img"/>
                    </span>
                    </div>
                </div>-->
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block">登 &nbsp;&nbsp;&nbsp;录</button>
                </div>
            </form>
        </div>
        <script>
            $('form').bind('valid.form', function(){
                $('form').ajaxSubmit({'success':function(r){
                    var resp = $.parseJSON(r);
                    if(resp.error===0)
                    { 
                        window.location.href='/index'
                    }else
                    {
                        alert(resp.message);
                    }
                }});
                return false;
            });
        </script>
        <script src="/script/jquery.form.js"></script>
        <script src="/script/jquery.validator.js"></script>
        <script src="/script/jquery.validator.zh.js"></script>
    </body>
</html>