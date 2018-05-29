# Yii2-swoole_websocket （demo简陋版）

PS：需要配置两个东西，一个数据库链接信息，在pdo_swoole_mysql.php中与index.php配置聊天记录储存根目录地址（由于demo简陋版暂时没有设置配置文件，请直接打开文件填写即可）；另外需要 建立一张 im 标识记录表，表结构在index.php最下面，可以直接使用

在命令行运行 php -f index.php 即可启动websocket（前提PHP已经安装swoole扩展并且可以使用，本人用的2.1.3）

服务器配置完毕后请求 http://xxx.xxx.xxx/index.php?r=im/index 即可聊天

basic 为 yii 项目目录

功能：

      基础聊天

      聊天历史记录
      
以后有时间会完善 （`0^0`）
