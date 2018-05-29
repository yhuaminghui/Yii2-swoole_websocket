<?php

class self_swoole_mysql
{
    private $host = '127.0.0.1';
    private $port = 3306;
    private $user = 'root';
    private $pass = 'Wo1tian3chi';
    private $database = 'yii2basic';
    private $charset = 'utf8';
    private $timeout = 2;
    public $config;

    public function __construct()
    {
        $this->init();
    }

    public function init()
    {
        $this->config = [
            'host'      =>  $this->host,
            'port'      =>  $this->port,
            'user'      =>  $this->user,
            'password'  =>  $this->pass,
            'database'  =>  $this->database,
            'charset'   =>  $this->charset,
            'timeout'   =>  $this->timeout
        ];
    }

    public function begin($sql)
    {
        $db = new swoole_mysql();

        $db->connect($this->config,function ($db,$r) use($sql){
            if ($r === false)
            {
                var_dump($db->connect_error,$db->connect_errno);
                die;
            }
            $db->query($sql, $a = function (swoole_mysql $db,$r){
                if ($r === false)
                {
                    var_dump($db->error,$db->errno);
                }
                else{
                    var_dump($db->affected_rows,$db->insert_id);
                }
                var_dump($r);
                $db->close();

            });
        });


    }


}

$sql = 'select * from country';
$a = new self_swoole_mysql();
$b = $a->begin($sql);

//var_dump($b);