<?php
namespace shell;
class binary
{
    private $binary; 
    private $method;
    private $env =array();
    private $cwd = null;
    private $stdin=null;
    
    public function __construct($binary)
    {
        $this->binary= $binary;
    }

    public function set_cwd($cwd)
    {
        $this->cwd  = empty($cwd)?null:(string)$cwd;
    }
    
    public function set_env(array $env = null)
    {
        $this->env  = $env;
    }
    
    public function set_stdin($stdin)
    {
        $this->stdin = $stdin;
    }

    public function __call($method, $arguments)
    {
        $this->method  = $method;
        $arr = array();
        foreach ($arguments as $v)
        {
            if(is_array($v))
            {
               $arr=array_merge($arr,$v); 
            }else
            {
                $arr[] = $v;
            }
        }
        return $this->exec($arr);
    }
    
    private function format_arguments($arguments)
    {
        if (is_string($arguments))
        {
            return $arguments;
        }else
        {
            $list = [];
            foreach ($arguments as $k => $v) 
            {
                $list[] = is_int($k)?$v:"$k $v";
            }
            return implode(' ', $list);
        }
    }

    /**
     * 
     * @param array|string $arguments
     * @param string $stdin
     */
    public function exec($arguments = array())
    {
        $cmd = trim(sprintf('%s %s %s', $this->binary, $this->method, $this->format_arguments($arguments)));
        $stdout_file = fopen('php://temp', 'r');
        $stderr_file = fopen('php://temp', 'r');
        $descriptorSpec = array(
            0 => array("pipe", "r"), // stdin is a pipe that the child will read from
            1 => $stdout_file,       // stdout is a temp file that the child will write to
            2 => $stderr_file        // stderr is a temp file that the child will write to
        );
        $pipes = array();
        $process = proc_open($cmd, $descriptorSpec, $pipes, $this->cwd, $this->env);
        $exit_code = 0;
        if (is_resource($process))
        {
            if (!is_null($this->stdin))
            {
                fwrite($pipes[0], (string) $this->stdin);
                $this->stdin=null;
            }
            fclose($pipes[0]);
            while (true)
            {
                $status = proc_get_status($process); 
                if($status['running']===false)
                {  
                    fseek($stdout_file, 0, SEEK_SET);
                    fseek($stderr_file, 0, SEEK_SET);
                    $stdout = rtrim(stream_get_contents($stdout_file)); 
                    $stderr = rtrim(stream_get_contents($stderr_file));
                    fclose($stdout_file);
                    fclose($stderr_file);
                    return array($exit_code, $stdout, $stderr);
                } 
                sleep(1);
            }
        }else
        {
            throw new \RuntimeException(sprintf('Cannot execute "%s"', $cmd));
        }
        fclose($stdout_file);
        fclose($stderr_file);
        return array($exit_code, $stdout, $stderr);
    }
}