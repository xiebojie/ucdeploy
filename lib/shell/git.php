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
class git
{

    private $binary;
    private $basepath;
    private $user;
    private $password;

    public function __construct($basepath, $user, $password)
    {
        $this->binary = new binary('git');
        
        $this->basepath = $basepath;
        $this->user = $user;
        $this->password = $password;
        $this->binary->set_cwd($basepath);
    }

    public function checkout($repository_url)
    {
         list($exit_code,$stdout,$stderr) = $this->binary->{'clone'}($repository_url, 
                $this->basepath);
         var_dump($exit_code);
         var_dump($stdout);
         var_dump($stderr);
    }
    
    public function update()
    {
        list($exit_code,$stdout,$stderr)=$this->binary->{'pull'}($this->basepath);
         var_dump($exit_code);
         var_dump($stdout);
         var_dump($stderr);
    }

    /**
     * Tries to find the root directory for a given repository path
     *
     * @param   string      $path       The file system path
     * @return  string|null             NULL if the root cannot be found, the root path otherwise
     */
    public static function findRepositoryRoot($path)
    {
        return FileSystem::bubble($path, function($p) {
                    $gitDir = $p . '/' . '.git';
                    return file_exists($gitDir) && is_dir($gitDir);
                });
    }

    /**
     * Returns the current commit hash
     *
     * @return  string
     */
    public function get_current_commit()
    {
        /** @var $result CallResult */
        list($exit_code, $stdout, $stderr) = $this->binary->{'rev-parse'}($this->basepath, array(
            '--verify',
            'HEAD'
        ));
        assert_success($exit_code == 0, 'Cannot rev-parse ', $stderr);
        return $stdout;
    }

    /**
     * Returns the current repository log
     *
     * @param   integer|null    $limit      The maximum number of log entries returned
     * @param   integer|null    $skip       Number of log entries that are skipped from the beginning
     * @return  array
     */
    public function get_log()
    {
        $this->binary->set_cwd($this->basepath);
        list($exit_code,$stdout,$stderr) = $this->binary->{'log'}('--pretty=format:"%cn|%cd|%s" -5 --date=iso');
        if($exit_code !=0)
        {
            throw new \Exception($stderr);
        }
        $loglist = array();
        foreach (explode("\n", $stdout) as $line)
        {
            list($author, $date,$msg) = explode('|', $line);
             $loglist[] = array(
                'revision' => '',
                'author' => $author,
                'date' => substr($date,0,17),
                'msg' => $msg
            );
        }
        return $loglist;
    }

}
