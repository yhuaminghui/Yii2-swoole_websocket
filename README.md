# Yii2-swoole_websocket （demo简陋版）

实际上用任何框架，或者原生的都行，不是必须用Yii框架的，但是需要实现对应的功能，特别是web/js/im_index.js中的功能项，此demo为分离性，没有强制关联在一起，

PS：需要配置两个东西，一个数据库链接信息，在pdo_swoole_mysql.php中与index.php配置聊天记录储存根目录地址（由于demo简陋版暂时没有设置配置文件，请直接打开文件填写即可）；另外需要 建立一张 im 标识记录表，表结构在index.php最下面，可以直接使用

在命令行运行 php -f index.php 即可启动websocket（前提PHP已经安装swoole扩展并且可以使用，本人用的2.1.3）

服务器配置完毕后请求 http://xxx.xxx.xxx/index.php?r=im/index 即可聊天

basic 为 yii 项目目录

功能：

      基础聊天

      聊天历史记录
      
以后有时间会完善 （`0^0`） yhuaminghui@sina.com 有兴趣的可以一起研究
