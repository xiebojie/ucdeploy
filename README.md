# ucdeploy
支持使用svn或git作为代码仓库的web项目发布工具，功能包括
1. 两步发布，先发布到回归机，回归测试通过后，再同步上线，支持发布回滚
2. 待发布的文件自动扫码并标示文件状态
3. 支持代码差异显示，方便上线前回差
4. 支持php语法检查

## 安装
1. 下载项目文件并解压
2. 修改nginx 配置文件将非静态页请求跳转到 www下的index.php 如下：
<pre><code>
server {
        listen       80;
        server_name  uchome.com;
        root           /Users/apple/project/uchome/www;

        location ~* \.(ico|css|js|gif|jpe?g|png|woff2?|ttf|swf|svg){

         }
         location / {
             fastcgi_pass   127.0.0.1:9000;
             fastcgi_index  index.php;
             fastcgi_param  SCRIPT_FILENAME  $document_root/index.php;
             include        fastcgi_params;
         }
     }
</code></pre>
3. 创建mysql数据库，并倒入根目录下的site.sql 文件
4. 修改www目录下的index.php 文件的数据库配置常量MYSQL_HOST、MYSQL_PORT、MYSQL_USER、MYSQL_PASSWD指向真实的数据库参数
5. 打开浏览器访问你配置的地址，使用admin账号登录默认密码是uchome-admin
补充：如果使用uchome 作为用户登陆服务器 请配置www目录下的index.php UC_APP_TOKEN和UCHOME_SIGNIN_URI 指向真实的uchome参数
