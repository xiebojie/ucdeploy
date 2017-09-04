<?php
/*
 * ucdeploy project
 *
 * Copyright 2017 xiebojie@qq.com
 * Licensed under the Apache License v2.0
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 */
namespace shell;
class svn 
{
    private $binary;
    private $user;
    private $password;
    private $basepath ;
    public function __construct($basepath, $user,$password)
    {
        $this->binary = new binary('svn');
        $this->basepath = $basepath;
        $this->user = $user;
        $this->password = $password;
        $this->binary->set_cwd($basepath);
    }
    
    public function checkout($repository_url)
    {
        list($exit_code,$stdout,$stderr) = $this->binary->{'checkout'}($repository_url, 
                $this->basepath, '--username '.$this->user,'--password '.$this->password,'--no-auth-cache');
        if($exit_code !=0)
        {
            throw new \Exception($stderr);
        }
        return $stdout;
    }
    
    //更新svn代码库，返回更新后的代码文件列表
    public function update()
    {
        list($code,,$stderr) = $this->binary->{'update'}($this->basepath,'--username '.$this->user,
                '--password '.$this->password,'--no-auth-cache', '--non-interactive','--force');
        if($code !=0)
        {
            throw new \Exception($stderr);
        }
    }
    
    /**
     * Returns the current commit hash
     * @return  string
     */
    public function get_current_commit()
    {
        list($code,$stdout,$stderr) = $this->binary->{'info'}($this->basepath, '--xml', '--revision HEAD',
                '--username '.$this->user,'--password '.$this->password,'--no-auth-cache');
        if($code !=0)
        {
            throw new \Exception($stderr);
        }
        $xml  = simplexml_load_string($stdout);
        if (!$xml) 
        {
            throw new \RuntimeException('Cannot read info XML for ');
        }
        $commit = $xml->xpath('/info/entry/commit[@revision]');
        if (empty($commit)) 
        {
            throw new \RuntimeException('Cannot read info XML for');
        }
        $commit = reset($commit);
        return (string)$commit['revision'];
    }
    
    /**
     * Returns the current repository log
     *
     * @param   integer|null    $limit      The maximum number of log entries returned
     * @param   integer|null    $skip       Number of log entries that are skipped from the beginning
     * @return  array
     * @throws  \RuntimeException
     */
    public function get_log($limit = 1, $skip = null)
    {
        $arguments  = array('--xml',  '--revision HEAD:0','--username '.$this->user,'--password '.$this->password,'--no-auth-cache');
        $skip   = ($skip === null) ? 0 : (int)$skip;
        if ($limit !== null) 
        {
            $arguments[] ='--limit '. intval($limit + $skip);
        }
        
        list($code,$stdout,$stderr) = $this->binary->{'log'}($arguments);
        if($code !=0)
        {
            throw new \Exception($stderr);
        }
        $xml  = simplexml_load_string($stdout);
        if (!$xml) 
        {
            throw new \RuntimeException('Cannot read log XML for');
        }
        $logEntries = new \ArrayIterator($xml->xpath('/log/logentry'));
        if ($limit !== null)
       {
            $logEntries = new \LimitIterator($logEntries, $skip, $limit);
        }
        $loglist = array();
        foreach ($logEntries as $item) 
        {
            $loglist[] = array(
                'revision' => (string) $item['revision'],
                'author' => (string) $item->author,
                'date' => date('Y-m-d H:i:s', strtotime((string) $item->date)),
                'msg' => (string) $item->msg
            );
        }
        return $loglist;
    }
    
     /**
     * Returns the current status of the working directory
     *
     * The returned array structure is
     *      array(
     *          'file'      => '...',
     *          'status'    => '...'
     *      )
     *
     * @return  array
     */
    public function get_status()
    {
        $arguments  = array('--xml', '--username '.$this->user,'--password '.$this->password,'--no-auth-cache');
        list($code,$stdout,$stderr) = $this->binary->{'status'}($arguments);
        if($code !=0)
        {
            throw  new \Exception($stderr);
        }
        $xml    = simplexml_load_string($stdout);
        if (!$xml) 
        {
            throw new \RuntimeException(sprintf('Cannot read status XML for "%s"',  $this->basepath));
        }
        $status = array();
        foreach ($xml->xpath('/status/target/entry') as $entry) 
        {
            $status[]   = array(
                'file'      => (string)$entry['path'],
                'status'    => (string)$entry->{'wc-status'}['item']
            );
        }
        return $status;
    }
}