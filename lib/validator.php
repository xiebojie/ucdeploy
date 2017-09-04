<?php 
/*
 * ucdeploy project
 *
 * Copyright 2017 xiebojie@qq.com
 * Licensed under the Apache License v2.0
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * $rules = array('name' => 'alpha|required', 'age'=> 'num--只能是数字|required--必须的', );
 * list($valdi_data,$errors)=validate($_POST, $rules);
 */
class validator
{
    private $preg_match_rule =array();
    private $func_match_rule=array();
    
    public function __construct()
    {
        $this->preg_match_rule['alnum'] = '#^([a-zA-Z0-9])+$#';
        $this->preg_match_rule['alpha'] = '#^([a-zA-Z\s])+$#';
        $this->preg_match_rule['email'] = '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/';
        $this->preg_match_rule['digits'] ='/^\d+$/';// 请输入数字
            
        $this->preg_match_rule['tel']= '/^(?:(?:0\d{2,3}[\- ]?[1-9]\d{6,7})|(?:[48]00[\- ]?[1-9]\d{6}))$/';//电话格式不正确
        $this->preg_match_rule['mobile']= '/^1[3-9]\d{9}$/';// 手机号格式不正确
        $this->preg_match_rule['qq'] = '/^[1-9]\d{4,}$/';// "QQ号格式不正确"
        $this->preg_match_rule['date'] = '/^\d{4}-\d{1,2}-\d{1,2}$/';//请输入正确的日期,例:yyyy-mm-dd
        $this->preg_match_rule['time']='/^([01]\d|2[0-3])(:[0-5]\d){1,2}$/';//"请输入正确的时间,例:14:30或14:30:00
        $this->preg_match_rule['idcard']='/^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])((\d{4})|\d{3}[Xx])$/';// "请输入正确的身份证号码
        $this->preg_match_rule['url']='/^(https?|ftp):\/\/[^\s]+$/i';//网址格式不正确
        $this->preg_match_rule['postcode']='/^[1-9]\d{5}$/';//邮政编码格式不正确
        $this->preg_match_rule['chinese']='/^[\u0391-\uFFE5]+$/';//请输入中文
        $this->preg_match_rule['username']='/^\w{3,12}$/';//请输入3-12位数字、字母、下划线
        $this->preg_match_rule['password']='/^[0-9a-zA-Z]{6,16}$/';//密码由6-16位数字、字母组成
        
        $this->func_match_rule['ip'] ='is_ip_match';
        $this->func_match_rule['url'] ='is_url_match';
        $this->func_match_rule['required'] ='is_required_match';
        $this->func_match_rule['max'] ='is_max_match';
        $this->func_match_rule['min'] ='is_min_match';
        $this->func_match_rule['num'] = 'is_num_match';
    }   

    public function register_preg_rule($rule_name, $expression )
    {
        $this->preg_match_rule[ $rule_name ] = $expression;
    }
   
    public function validate($fields, $rules)
    {
        $valid_data = array();
        $errors = array();
        foreach ($rules as $field => $ruleString)
        {
            if (isset($fields[$field]) && is_array($fields[$field]))
            {
                $fields[$field] = array_map('strip_tags', $fields[$field]);
                $value_list = $fields[$field];
            } else if (isset($fields[$field]))
            {
                $fields[$field] = strip_tags($fields[$field]);
                $value_list = array($fields[$field]);
            } else
            {
                $value_list = array(null);
            }
            foreach ($value_list as $value)
            {
                foreach (explode('|', $ruleString) as $rule)
                {
                    list($rule_name, $rule_param, $message) = $this->parse_rule($rule, $field);
                    if($rule_name=='optional' && empty($value))
                    {
                        unset($errors[$field]);
                        break;
                    }
                    array_unshift($rule_param, $value);
                    if (isset($this->preg_match_rule[$rule_name]) && !preg_match($this->preg_match_rule[$rule_name], $value))
                    {
                        $errors[$field] = $message;
                    } else if (isset($this->func_match_rule[$rule_name]) && !call_user_func_array($this->func_match_rule[$rule_name], $rule_param))
                    {
                        $errors[$field] = $message;
                    }
                }
            }
            if (!isset($errors[$field]) && isset($fields[$field]))
            {
                $valid_data[$field] = $fields[$field];
            }
        }
        return array($valid_data, $errors);
    }
  
    private function parse_rule($rule_str, $field)
    {
        if (strpos($rule_str, '--') !== false)
        {
            list($rule_str, $message) = explode('--', $rule_str);
        } else
        {
            $message = $field . ' is invalid@'.$rule_str;
        }
        if (strpos($rule_str, ':') !== false)
        {
            list($rule_name, $param_str) = explode(':', $rule_str);
            $rule_param = explode(',', $param_str);
        } else
        {
            $rule_name = $rule_str;
            $rule_param = array();
        }
        return array($rule_name, $rule_param, $message);
    }
}

function is_ip_match($value)
{
    return filter_var($value, FILTER_VALIDATE_IP) !== false ? true : false;
}

function is_url_match($value)
{
    return filter_var($value, FILTER_VALIDATE_URL) !== false ? true : false;
}

function is_required_match($value)
{
    return strlen(trim($value)) > 0;
}

function is_max_match($value, $length)
{
    return mb_strlen($value) <= $length;
}

function is_min_match($value, $length)
{
    return mb_strlen($value) >= $length;
}

function is_num_match($value)
{
    return is_numeric($value);
}