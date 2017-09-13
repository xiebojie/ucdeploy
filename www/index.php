<?php
/*
 * ucdeploy project
 *
 * Copyright 2017 xiebojie@qq.com
 * Licensed under the Apache License v2.0
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 */
define('BASEPATH', dirname(dirname(__FILE__)).'/');
define('TPLPATH', BASEPATH.'tpl/');
date_default_timezone_set('PRC');
define('TPL_CACHE_PATH', BASEPATH.'tpl/.cache/');
ini_set('session.save_path', '/tmp/');
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

ini_set('default_socket_timeout', -1);

define('MYSQL_HOST','localhost');
define('MYSQL_PORT','3306');
define('MYSQL_USER','root');
define('MYSQL_PASSWD','ppiao');
define('MYSQL_DBNAME','ucdeploy');

define('UC_APP_TOKEN','');
define('UCHOME_SIGNIN_URI','');

function uchome_ldap_signin()
{
    return array(
        'user_id'=>1,
        'username'=>'xiebojie'
    );
}

function autoload($class)
{
    if (stripos($class, "redis") === false)
    {
        $path = BASEPATH . 'model/' . $class . '.php';
        if (file_exists($path))
        {
            require $path;
        }else
        {
            $class = str_replace("\\", '/', $class);
            require BASEPATH . 'lib/' . $class . '.php';
        }
    }
}

spl_autoload_register('autoload', true, false);

function error_handler($errno, $errstr, $errfile, $errline)
{
}

function get_mydb()
{
    static $dbh = null;
    if (!$dbh)
    {
        //$dbh = new mysql(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWD, MYSQL_DBNAME, MYSQL_PORT);
        $dbh = new mysql('localhost', 'root', 'ppiao', 'ucdeploy', 3306);
    }
    return $dbh;
}


function http_request($url, $method = "GET", $payload = null, $user = '', $passwd = '')
{
    $url_parts = parse_url($url);
    $conn = curl_init();
    curl_setopt($conn, CURLOPT_URL, $url);
    curl_setopt($conn, CURLOPT_TIMEOUT, 5);
    curl_setopt($conn, CURLOPT_PORT, $url_parts['port']);
    curl_setopt($conn, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($conn, CURLOPT_CUSTOMREQUEST, strtoupper($method));
    curl_setopt($conn, CURLOPT_FORBID_REUSE, 0);
    if (!empty($user) && !empty($passwd))
    {
        curl_setopt($conn, CURLOPT_USERPWD, $user . ':' . $passwd);
    }
    if (is_array($payload) && count($payload) > 0)
    {
        curl_setopt($conn, CURLOPT_POSTFIELDS, http_build_query($payload));
    }else
    {
        curl_setopt($conn, CURLOPT_POSTFIELDS, $payload);
    }
    $response = curl_exec($conn);
    $htppcode = curl_getinfo($conn, CURLINFO_HTTP_CODE);
    if ($htppcode == 200 || $response !== false)
    {
        $data = json_decode($response, true);
        if (!$data)
        {
            return $response;
        }
        return $data;
    }else
    {
        trigger_error( curl_errno($conn),E_USER_WARNING);
    }
    return $data;
}


function rscandir($dir)
{
    $file_list = array();
    $stack[] = $dir;
    while ($stack)
    {
        $current_dir = array_pop($stack);
        if ($dh = opendir($current_dir))
        {
            while (($file = readdir($dh)) !== false)
            {
                if ($file !== '.' && $file !== '..' &&!preg_match('/^\./', $file))
                {
                    $current_file = "{$current_dir}/{$file}";
                    if (is_file($current_file))
                    {
                        $file_list[] = str_replace("{$dir}/", '', "{$current_dir}/{$file}");
                    }elseif (is_dir($current_file))
                    {
                        $stack[] = $current_file;
                        $file_list[] = str_replace("{$dir}/", '', "{$current_dir}/{$file}/");
                    }
                }
            }
        }
    }
    return $file_list;
}

function scandir_tree($dir,$basedir)
{
    $dir  = rtrim($dir,'/');
    $file_list = array();
    $dh = opendir($dir);
    if ($dh)
    {
        while (($file = readdir($dh)) !== false)
        {
            if ($file !== '.' && $file !== '..' &&!preg_match('/^\./', $file))
            {
                if (is_file($dir.'/'.$file))
                {
                    $file_list[str_replace($basedir,'',$dir.'/'.$file)] = $file;
                }elseif (is_dir($dir.'/'.$file))
                {
                    $file_list[str_replace($basedir,'',$dir.'/'.$file)] = scandir_tree($dir.'/'.$file,$basedir);
                }
            }
        }
    }
    return $file_list;
}


function assert_success($is_success,$message,$stderr)
{
    if($is_success)
    {
        throw new Exception($message .'stderr:'.$stderr);
    }
}

function get_repository($type,$path, $user,$password)
{
    if($type=='svn'){
        $repository = new shell\svn($path,$user,$password);
    }else{
        $repository = new shell\git($path, $user, $password);
    }
    return $repository;
}

function ssl_encrypt($source, $key)
{
    $maxlength = 52;
    $output = '';
    while ($source)
    {
        $input = substr($source, 0, $maxlength);
        $source =substr($source,$maxlength);
        if(openssl_public_encrypt($input, $encrypted, $key))
        {
            $output.=$encrypted;
        }else
        {
            trigger_error("ssl encrypt failed ");
        }
    }
    return base64_encode($output);
}

function ssl_decrypt($source, $key)
{
    $maxlength = 64;
    $output = '';
    $source = base64_decode($source);
    while ($source)
    {
        $input = substr($source, 0, $maxlength);
        $source = substr($source, $maxlength);
        if(openssl_private_decrypt($input, $out, $key))
        {
            $output.=$out;
        }else
        {
            trigger_error("ssl decrypt failed");
        }
    }
    return $output;
}

function uri_route()
{
    $uri = parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
    $segements = explode('/', trim($uri,'/'));
    $dir = '';
    while(true)
    {
        if(count($segements)>0&& is_dir(BASEPATH.'ctrl/'.$dir.$segements[0]))
        {
            $dir .=$segements[0].'/';
            array_shift($segements);
        } else 
        {
            break;
        }
    }
    $class = isset($segements[0])?$segements[0]:'index';
    $methods = isset($segements[1])&& !is_numeric($segements[1])?$segements[1]:'index';
    
    return array($dir, $class, $methods,  array_slice($segements, 2));
}

function form_filter_parse($field_list, $data)
{
    $where = array();
    foreach ($field_list as $_field => $fielter_list)
    {
        if (isset($data[$_field]) && is_string($data[$_field]) && $data[$_field] != '')
        {
            $input = addslashes($data[$_field]);
            $field_where = array();
            $tpl = "%s ='%s'";
            $column_list = array();
            $glue = 'AND';
            foreach (explode('|', $fielter_list) as $_filter)
            {
                list($key, $val) = explode(':', $_filter);
                if ($key == 'column')
                {
                    $column_list = explode(',', $val);
                } else if ($key == 'compare')
                {
                    if ($val == 'equal')
                    {
                        $tpl = "%s=%s";
                    } elseif ($val == 'like')
                    {
                        $tpl = "%s LIKE '%%%s%%'";
                    } else if ($val == 'date_start')
                    {
                        $tpl = "%s >= '%s'";
                    } else if ($val == 'date_end')
                    {
                        $tpl = "%s <='%s'";
                    }
                } else if ($key == 'glue')
                {
                    $glue = $val;
                }
            }
            foreach ($column_list as $_column)
            {
                $field_where[] = sprintf($tpl, $_column, $input);
            }
            $where[] = !empty($field_where) && count($field_where) > 1 ? sprintf("(%s)", implode(" $glue ", $field_where)) : $field_where[0];
        }
    }
    return $where;
}

function show404()
{
    header('Status: 404 Not Found',true,404);
    include BASEPATH.'tpl/404.php';
    exit;
}

function show_err($msg='')
{
    header('Status:500',true,500);
    extract(array('msg'=>$msg));
    include TPLPATH. '500.php';
    exit;
}

function redirect($url)
{
    $req=  parse_url($_SERVER['REQUEST_URI']);
    if($req['path'] !=$url)
    {
        header('Location:'.$url, true, 301);
        exit;
    }
}

function str_replace_star($str,$star_len=3)
{
    $len = mb_strlen($str);
    if($len>$star_len+2)
    {
        return mb_ereg_replace(mb_substr($str, ceil(($len-$star_len)/2), $star_len), str_pad('', $star_len,'*'),$str);
    }
    return $str;
}

/***********************************初始化控制器并执行该方法*******************************************************/

!isset($_SESSION) && session_start();
list($DIR, $CLASS, $ACT, $PARAMS) = uri_route();

include BASEPATH.'lib/ctrl.php';
$PATH = BASEPATH.'ctrl/'.strtolower($DIR.$CLASS).'_ctrl.php';

if (!file_exists($PATH))
{
    show404();
} else 
{
    include $PATH;
    $CLASS=$CLASS.'_ctrl';
    $OBJECT = new $CLASS;
    if($ACT =='list')
    {
        $ACT = 'index';
    }
    if (!method_exists($OBJECT, $ACT))
    {
        show404();
    }
    $resp = call_user_func_array(array($OBJECT, $ACT), $PARAMS);
    if(is_array($resp))
    {
        echo json_encode($resp);
    }
}