<?php
/*!
 * ucdeploy project
 *
 * Copyright 2017 xiebojie@qq.com
 * Licensed under the Apache License v2.0
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 */
namespace shell;

class rsync
{
    private $binary;
    private $archive = true;
    private $skip_newer_files = false;
    private $follow_sym_links = true;
    private $dry_run = false;
    private $verbose = false;
    private $delete_from_target = false;
    private $delete_excluded = false;
    private $exclude = array();
    private $recursive = true;
    private $times = false;
    private $compression = false;
    private $remove_source = false;
    private $compare_dest = false;
    private $ssh_user = '';
    private $ssh_private_key= '';
   
    public function __construct($ssh_user, $ssh_private_key)
    {
        if(is_executable('/usr/bin/rsync'))
        {
            $this->binary = new binary('/usr/bin/rsync');
        } else
        {
            $this->binary = new binary('rsync');
        }
        $this->binary->set_env(array('SSH_AUTH_SOCK'=>'/run/user/1000/keyring-rpZS5C/ssh'));
        $this->ssh_user = $ssh_user;
        $this->ssh_private_key = $ssh_private_key;
        //$this->set_delete_from_target(true);
    }
    
    
    public function sync($ssh_host,$origin,$target)
    {
        $options = $this->get_options();
        return $this->binary->exec(sprintf("{$options} --rsh '/usr/bin/ssh ' $origin %s@%s:$target",  $this->ssh_user,  $ssh_host));
    }
    
    public function set_skip_newer_files($skip_newer_files)
    {
        $this->skip_newer_files= $skip_newer_files;
    }

    public function set_follow_sym_links($follow_sym_links)
    {
        $this->follow_sym_links = $follow_sym_links?true:false;
    }

    public function set_dry_run($dry_run)
    {
        $this->dry_run= $dry_run?true:false;
    }

    public function set_verbose($verbose)
    {
        $this->verbose = $verbose?true:false;
    }

    public function set_delete_excluded($delete_excluded)
    {
        $this->delete_excluded = $delete_excluded?true:false;
    }

    public function set_delete_from_target($delete_from_target)
    {
        $this->delete_from_target = $delete_from_target?true:false;
    }
    
    public function set_exclude(array $exclude)
    {
        if(is_array($exclude))
        {
           $this->exclude = $exclude; 
        } else
        {
            $this->exclude = array($exclude); 
        }
    }

    public function set_recursive($recursive)
    {
        $this->recursive = $recursive?true:false;
    }

    public function set_times($times)
    {
        $this->times = $times;
    }

    public function set_compression($compression)
    {
        $this->compression = $compression?true:false;
    }

    public function set_remove_source($remove_source)
    {
        $this->remove_source =$remove_source?true:false;
    }

    public function set_compare_dest($dest)
    {
        $this->compare_dest = $dest?true:false;
    }
    
    private function get_options()
    {
        $options = array();
        if ($this->skip_newer_files)
        {
            $options[] = '-u';
        }
        if ($this->follow_sym_links){
            $options[]="-L";
        }
        //显示哪些文件将被传输
        if ($this->dry_run){
            $options[] = '-n';
        }
        if ($this->verbose){
            $options[]='-v';
        }
        if ($this->compression)
        {
            $options[] ='-z';
        }
       
        if ($this->times)
        {
            $options[] ='--times';
        }
        if ($this->delete_from_target)
        {
            $options[] = '--delete';
        }
        if ($this->remove_source)
        {
            $options[] = '--remove-source-files';
        }
        if ($this->delete_excluded)
        {
            $options[] = '--delete-excluded';
        }
       //同样比较DIR中的文件来决定是否需要备份
        if ($this->compare_dest)
        {
            $options[] = '--compare-dest '.$this->compare_dest;
        }
        if (!empty($this->exclude))
        {
            foreach ($this->exclude as $excluded) 
            {
                $options[] = sprintf('--exclude=%s',$excluded);
            }
        }
        if ($this->archive)
        {
            $options[] ='-a';
        }
        if (!$this->archive && $this->recursive)
        {
            $options[]='-r';
        }
        return implode(' ', $options);
    }   
}