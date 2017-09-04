<?php
class mysql
{
    private $dbhost;
    private $dbuser;
    private $dbpwd;
    private $dbname;
    private $dbport;

    public function __construct($dbhost, $dbuser, $dbpwd, $dbname, $port = 3306) 
    {
        $this->dbhost = $dbhost;
        $this->dbuser = $dbuser;
        $this->dbpwd = $dbpwd;
        $this->dbname = $dbname;
        $this->dbport = $port;
    }

     /**
     * 只有在需要的情况下，才去建立数据库连接
     * @param string $name
     */
    public function __get($name) 
    {
        if ($name === 'dbh') 
        {
            $this->dbh = mysqli_connect(
                    $this->dbhost, 
                    $this->dbuser, 
                    $this->dbpwd, 
                    $this->dbname, 
                    $this->dbport,
                    '/private/tmp/mysql.sock'
            );
            if(!$this->dbh)
            {
                trigger_error("can't connect to mysql server", E_ERROR);
            }
            mysqli_query($this->dbh, 'SET names utf8');
            return $this->dbh;
        }
    }

    public function fetch($sql) 
    {
        if (stripos(trim($sql), 'select') !== 0)
        {
            exit('only select allowed for fetch sql');
        }
        $query = $this->query($sql);
        $row = mysqli_fetch_assoc($query);
        mysqli_free_result($query);
        return $row;
    }

    public function fetch_all($sql) 
    {
        if (stripos(trim($sql), 'select') === false)
        {
            //exit('only select allowed for fetch sql');
        }
        $query = $this->query($sql);
        $rows = array();
        while ($row = mysqli_fetch_assoc($query)) 
        {
            $rows[] = $row;
        }
        mysqli_free_result($query);
        return $rows;
    }

    public function fetch_col($sql) 
    {
        if (stripos(trim($sql), 'select') !== 0)
        {
            exit('only select allowed for select sql');
        }
        $query = $this->query($sql);
        $row = mysqli_fetch_array($query);
        return $row[0];
    }

    public function insert($sql) 
    {
        if(stripos(trim($sql), 'insert') !==0)
        {
            exit('only insert allowed for insert sql');
        }
        $this->query($sql);
        return mysqli_insert_id($this->dbh);
    }

    public function delete($sql) 
    {
        if(!preg_match('/where/i', $sql)&&!preg_match('/limit/i', $sql))
        {
            exit('no where or limit matched for delete sql');
        }
        $this->query($sql);
        return mysqli_affected_rows($this->dbh);
    }

    public function replace($sql) 
    {
        if(!preg_match('/where/i', $sql))
        {
            exit('no where  matched for update sql');
        }
        $this->query($sql);
        return mysqli_affected_rows($this->dbh);
    }
    
    public function query($sql)
    {
        $query = mysqli_query($this->dbh,$sql);
        if (mysqli_errno($this->dbh))
        {
            $error = mysqli_error($this->dbh);
            echo $error,"\n",$sql;
        }
        if(!empty($_GET['dbdebug']) && $_SESSION['username']=='xiebojie')
        {
            echo $sql,"\n";
        }
        return $query;
    }
    
    public function ping()
    {
        mysqli_ping($this->dbh);
    }
}