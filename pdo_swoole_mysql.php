<?php
class pdo_swoole_mysql
{
    private $dbms = 'mysql';
    private $host = '';
    private $port = 3306;
    private $database = '';
    private $username = '';
    private $password = '';
    private $charset = 'utf8';
    private $con;
    public function __construct()
    {
        $this->_init();
    }

    public function _init()
    {
        $user = $this->username;      //数据库连接用户名
        $pass = $this->password;          //对应的密码
        $dsn="$this->dbms:host=$this->host;dbname=$this->database";

        $this->con = new PDO($dsn,$user,$pass);
    }

    public function query($sql)
    {
        $pdoStatement = $this->con->query($sql);
        return $pdoStatement->fetch(PDO::FETCH_ASSOC);
    }

    public function exece($sql)
    {
        return $this->con->exec($sql);
    }

}

//$sql = 'select * from country';
//$pdo = new pdo_swoole_mysql();
//$res = $pdo->query($sql);
//var_dump($res);
