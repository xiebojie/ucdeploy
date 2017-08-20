<?php
/*!
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

include BASEPATH.'/daemon/uboot.inc.php';

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