<?php
/*
 * ucdeploy project
 *
 * Copyright 2017 xiebojie@qq.com
 * Licensed under the Apache License v2.0
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 */
class layout
{
    private $block_list =array();
    private $extend_tpl='';
    
    public function compile($tpl_file)
    {
        if(!file_exists(TPLPATH.$tpl_file))
        {
           trigger_error("tpl file($tpl_file) not found",E_WARNING);
        } 
        $this->extend_tpl='';
        $code = file_get_contents(TPLPATH.$tpl_file);
        $code = preg_replace_callback('/\{\%\s*extend\s*([^\%]*)\s*\%\}/i',array($this,'extend_callback'),$code);
        $code = preg_replace_callback('/\{\%\s*(\$.*)\s*\%\}/',array($this,'output_callback'),$code);
        $code = preg_replace_callback('/\{\%\s*block\s*(\w*)\s*\%\}([.\n\S\{\}\s]*)\{\%\s*endblock\s*\%\}/i',array($this,'block_callback'),$code);
        if(!empty($this->extend_tpl))
        {
            return $this->compile($this->extend_tpl);
        }else
        {
            return $code;  
        }
    }
    
    protected function block_callback($matches)
    {
        $block_name = empty($matches[1])?'':$matches[1];
        $block_content = empty($matches[2])?'':trim($matches[2]);
        if(empty($block_content) && isset($this->block_list[$block_name]))
        {
            return $this->block_list[$block_name];
        }else if(empty($this->extend_tpl))//block output direct
        {
            return $block_content;
        }else //block define
        {
            $this->block_list[$block_name] = $block_content;
            return '';
        }
    }
    
    protected function extend_callback($matches)
    {
        $this->extend_tpl = empty($matches[1])?'':$matches[1];
        return '';
    }
    
    //{%$a['b']['c']|upper:''|raw:'':$ss|default:''%}  
    protected function output_callback($matches)
    {
        $anti_xss = true;
        $output = isset($matches[1])?$matches[1]:'';
        
        foreach(explode('|',$output) as $i=>$func)
        {
            if($i==0)
            {
                $str = $func;
            }else
            {
                $param = explode(':', $func);
                $func = array_shift($param);
                array_unshift($param, $str);
                if($func=='default')
                {
                    $str = sprintf('empty(%s)?%s:%s',$str,$param[1], $str);
                }else if($func=='raw')
                {
                    $anti_xss = false;
                }else
                {
                    $str = $func.'('.implode(',',$param).')';
                }
            }
        }
        if($anti_xss)
        {
            $str= sprintf('htmlspecialchars(%s)',$str);
        }
        return sprintf('<?php echo %s;?>', $str);
    }
}