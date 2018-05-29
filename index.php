<?php
// swoole websocket
require_once __DIR__ . '/pdo_swoole_mysql.php';
class swoole_websocket{

    private $host = '0.0.0.0'; // 监听地址 默认任意地址
    private $port = 8008;  // 监听端口 默认8008
    private static $ws; // 储存连接标识
    private $chatDir; // 聊天记录储存根目录
    
    public function __construct($chatDir)
    {
        $this->chatDir = $chatDir;
        // 初始化连接
        $this->init();
        // 监听链接
        $this->open();
        // 监听请求数据
        $this->message();
        // 监听关闭请求
        $this->close();
    }

    public function init()
    {
        //创建websocket服务器对象，监听0.0.0.0:9502端口
        self::$ws = new swoole_websocket_server($this->host, $this->port);
    }

    public function begin()
    {
        // 启动
        self::$ws->start();
    }

    //监听WebSocket连接打开事件
    public function open()
    {
        self::$ws->on('open', function ($ws, $request) {
            $ws->push($request->fd, $this->returnJson(['error'=>20000,'msg'=>'hello, welcome','data'=>[]]));
        });
    }

    //监听WebSocket消息事件
    /*
     * 逻辑思维：
     * 1，利用用户名+用户ID生成一个唯一的key
     * 2，将用户唯一key与用户fd组成key=>value键值对，存入数据库
     * 3，将聊天记录存为文件形式
     * 4，每次点击对应用户时，调用websocket链接open时将聊天记录返给前台做展示
     */
    public function message()
    {
        self::$ws->on('message', function ($ws, $frame) {
            echo "Message: {$frame->data}\n";
            $requestData = json_decode($frame->data,true);

            // 验证是否有参数，如果没有参数则返回空
            if (! empty($returnData = $this->checkData($requestData)))
            {
                $ws->push($frame->fd,$returnData);
                return;
            }

            // 判断当前数据类型需要做什么事情
            switch ($requestData['type'])
            {
                case 'register': // 注册标识
                    // 1，查询当前用户标识表 是否已经存在数据
                    //      true：修改链接标识
                    //      false：添加标识

                    // 初始化数据链接符
                    $db = new pdo_swoole_mysql();

                    // 定义标识
                    $flag = $requestData['self']['id'];
                    // 查询
                    $sql = 'select * from fd where flag = ' . $flag;
                    $isAlready = $db->query($sql); // 自动释放链接
                    if (empty($isAlready))
                    {
                        // 加入新数据
                        $sql = 'insert into fd(flag,fd,status,create_time,update_time) VALUES('.$flag.','.$frame->fd.',1,'.time().','.time().')';
                    }else{
                        // 更新当前数据
                        $sql = 'update fd set fd = ' . $frame->fd . ' where id = ' . $isAlready['id'];
                    }
                    var_dump($sql);
                    $db->exece($sql);
                    break;
                case 'chat_recode': // 拉取聊天记录
                // 获取到的数据格式为
                /*
                {
                    type          :   'chat_recode',
                    self          :   {
                        username      :   self_username,
                        id            :   self_id
                    },
                    to            :   {
                        username      :   to_username,
                        id            :   to_id
                    }
                };
                */
                // 1，根据 self 用户ID 获取对应的用户 im 文件夹
                // 2，根据 to 用户id 获取对应的文件名
                // 3，获取文件内容，格式请看 case 为 single_im中记录聊天记录中的数据格式
                    $chat = $requestData['self']['id'] . '/' . date('Y-m-d') . '/' . $requestData['to']['id'] . '.log' ;
                    $chatDir = $this->chatDir . $chat;

                    if(file_exists($chatDir))
                    {
                        $chatContent = file_get_contents($chatDir);
                    }else{
                        $chatContent = '{}';
                    }
                    
                    // 4，获取当前用户的fd，返回数据
                    $ws->push($frame->fd,$this->returnJson(['error'=>20000,'msg'=>'success','data'=>json_decode($chatContent,true)]));
                    break;
                case 'single_im':
                    // 接收到的数据 格式 为
                    /*
                    {
                        type        :   'single_im',
                        content     :   content,
                        self        :   {
                            username    :   self_username,
                            id          :   self_id
                        },
                        to          :   {
                            username    :   to_username,
                            id          :   to_id
                        }
                    };
                     */
                    // 1，先查找 self 用户名与用户ID是否处于聊天状态
                    // 2，如果在聊天状态，查找 to 用户名与用户ID 是否处于聊天状态

                    // 初始化数据库链接符
                    $selfFlag =  $requestData['self']['id'];
                    $db = new pdo_swoole_mysql();
                    $selfIsOnline = 'select * from fd where flag = ' . $selfFlag;
                    $selfFdInfo = $db->query($selfIsOnline);

                    if ($selfFdInfo['status'] == 1)
                    {
                        // 在线
                        $toFlag = $requestData['to']['id'];
                        $toIsOnline = 'select * from fd where flag = ' . $toFlag;

                        $toFdInfo = $db->query($toIsOnline);

                        if ($toFdInfo['status'] == 1)
                        {
                            // 在线
                            // 3，如果处于，则拿到对应的 fd 标识，发送数据 数据格式为
                            /*
                             * 返回 的数据 格式为
                             * {
                             *      'type'  :   'single_im', // 数据类型
                             *      'data'  :   {
                             *              id          :   xx // self 用户id
                             *              content     :   xx // to 聊天内容
                             *          }
                             * }
                             */
                            $returnData = [
                                'type'      =>  'single_im',
                                'data'      =>  [
                                    'id'        =>  $selfFlag,
                                    'content'   =>  $requestData['content']
                                ]
                            ];

                            // 当前是否存在 to 用户的链接标识
                            if ($ws->exists($toFdInfo['fd']))
                            {
                                @$ws->push($toFdInfo['fd'],$this->returnJson(['error'=>20000,'msg'=>'success','data'=>$returnData]));
                            }else{
                                // 返回数据 说明对方不在线,发送的数据将离线保存
                                $ws->push($frame->fd,$this->returnJson(['error'=>20001,'msg'=>'The other is not online,send data will be saved offline','data'=>[]]));
                            }

                            // 4，记录用户的聊天记录，每个用户都需要记录；格式为
                            /*
                             * {
                             *      'type'  :   'char_recode', // 数据类型
                             *      'data'  :   [
                             *          {
                             *              id          :   xx // 用户id
                             *              content     :   xx // 聊天内容
                             *          },
                             *          ...
                             *      ]
                             * }
                             */
                            // 获取原有的文件内容
                            $selfChatDir = $this->chatDir . $selfFlag . '/' . date('Y-m-d') . '/' . $toFlag . '.log';
                            $toChatDir = $this->chatDir . $toFlag . '/' . date('Y-m-d') . '/' . $selfFlag . '.log';

                            $chatRecode = [
                                'id'        =>  $selfFlag,
                                'content'   =>  $requestData['content']
                            ];

                           $this->chatRecode($selfChatDir,$chatRecode);
                           $this->chatRecode($toChatDir,$chatRecode);
                            // 5，保存文件夹名为，当前用户ID组成的文件夹名字，文件名为对应用户ID

                        }else{
                            // 对方不在线 状态码 42000
                            $returnData = [
                                'error'     =>  42000,
                                'msg'       =>  'The other is not online',
                                'data'      =>  []
                            ];
                            $ws->push($frame->fd,$this->returnJson($returnData));
                        }
                    }else{
                        // 当前链接失效 状态码 41000
                        $returnData = [
                            'error'     =>  41000,
                            'msg'       =>  'The connection has been disconnected,place flush Or reopen',
                            'data'      =>  []
                        ];
                        $ws->push($frame->fd,$this->returnJson($returnData));
                    }

                    break;
                case 'im':
                    // 链接成功状态码 20000
                    $ws->push($frame->fd,  $this->returnJson(['error'=>20000,'msg'=>'success','data'=>[]]));
                    break;
            }
        });
    }

    // 关闭
    public function close()
    {
        //监听WebSocket连接关闭事件
        self::$ws->on('close', function ($ws, $fd) {
            echo "client-{$fd} is closed\n";
        });
    }

    // 返回值处理
    public function returnJson(array $data)
    {
        if (is_array($data))
        {
            return json_encode($data);
        }else{
            $error = [
                'error'     =>  1,
                'msg'       =>  'server error',
                'data'      =>  []
            ];
            return json_encode($error);
        }
    }

    // 验证
    public function checkData($requestData)
    {
        // 请求数据为空 状态码 40000
        if (empty($requestData))
        {
            return $this->returnJson(['error'=>40000,'msg'=>'data is null','data'=>[]]);
        }

        // 请求数据类型为空 状态码 40001
        if (empty($requestData['type']))
        {
            return $this->returnJson(['error'=>40001,'msg'=>'type is null','data'=>[]]);
        }

        // 请求数据self为空 状态码 40002
        if (empty($requestData['self']))
        {
            return $this->returnJson(['error'=>40002,'msg'=>'self is null','data'=>[]]);
        }

        // 请求数据self username为空 状态码 40003
        if (empty($requestData['self']['username']))
        {
            return $this->returnJson(['error'=>40003,'msg'=>'self username is null','data'=>[]]);
        }

        // 请求数据 self id 为空 状态码 40004
        if (empty($requestData['self']['id']))
        {
            return $this->returnJson(['error'=>40004,'msg'=>'self id is null','data'=>[]]);
        }
    }

    // 记录聊天记录
    /*
     * @dir 记录地址
     * @data 记录数据 一维关联数组
     */
    public function chatRecode($dir,$data)
    {
        if(! file_exists($dir))
        {
            mkdir(substr($dir,0,strrpos($dir,'/')),0755);
            file_put_contents($dir,'');
        }
        $alreadyChat = file_get_contents($dir);

        if(empty($alreadyChat))
        {
            $chatRecode = [
                'type'      =>   'chat_recode',
                'data'      =>  [
                    $data
                ]
            ];
            file_put_contents($dir,json_encode($chatRecode));
        }else{
            $chatData = json_decode($alreadyChat,true);
            $chatRecode = $data;
            array_push($chatData['data'],$chatRecode);
            file_put_contents($dir,json_encode($chatData));
        }
    }
}


$chatDir = ''; // 聊天记录储存根目录
$ws = new swoole_websocket($chatDir);
$ws->begin(); // 运行

// 链接 标识 表
//drop table if exists fd;
//create table fd(
//    id int not null auto_increment primary key,
//    flag int not null default 0 comment '用户标识，用户名_用户ID，注意当用户改名称的时候需要修改此字段',
//    fd int not null default 0 comment '用户链接标识，当用户每次链接的时候需要修改此字段',
//    status tinyint not null default 0 comment '当前状态，0下线；1上线；2；繁忙；3，未开启通知',
//    create_time int not null default 0 comment '创建时间，标识用户第一次使用im的时间',
//    update_time int not null default 0 comment '更新时间，用户最近一次链接的时间',
//    key `flag` (`flag`),
//    key `fd` (`fd`)
//)charset utf8 engine innodb;


