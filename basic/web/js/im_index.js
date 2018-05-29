var wsServer = 'ws://xxx.xxx.xxx:8008';
var websocket = new WebSocket(wsServer);

// 此链接主要功能
/*
 1，链接服务器
 2，注册当前用户
 */
websocket.onopen = function (evt) {
    console.log("Connected to WebSocket server.");
    // 将用户名与用户ID 链接起来传输
    var username = $('#username').val();
    var id = $('#userid').val();

    var register = {
        type          :    'register',
        self      :   {
            username      :   username,
            id            :   id
        }
    };
    console.log(register);

    var jsonData = JSON.stringify(register);
    websocket.send(jsonData)
};

$('.friend-list').click(function () {
    // 清空聊天窗口的数据
    $('.show-content').find('div').remove();

    var _this = $(this);
    // 先判断是否已经存在对应聊天
    var select = $('.select');
    if(select.length === 0)
    {
        // 当前没有聊天，给点击的用户聊天
        _this.addClass('select');
    }else if(_this.hasClass('select')){
        return false;
    }else{
        $('.select').removeClass('select');
        _this.addClass('select');
    }

    // 拉取聊天记录
    // 将用户名与用户ID 链接起来传输
    var self_username = $('#username').val();
    var self_id = $('#userid').val();

    // 对应的用户
    var to_username = _this.data('username');
    var to_id = _this.data('id');

    // 拉取聊天记录，回显数据
    var data = {
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
    websocket.send(JSON.stringify(data));
})




websocket.onmessage = function (evt) {
    // 将接收到的数据放入聊天窗口
    // 判断当前的数据类型
    var responseData = JSON.parse(evt.data);

    if(parseInt(responseData.error) != 20000)
    {
        alert(responseData.msg);
        return;
    }

    var data = responseData.data;
    console.log(data);

    switch (data.type)
    {
        case 'chat_recode': // 拉取聊记录
            /*
             * 返回 的数据 格式为
             * {
             *      'type'  :   'char recode', // 数据类型
             *      'data'  :   [
             *          {
             *              id          :   xx // 用户id
             *              content     :   xx // 聊天内容
             *          },
             *          {
             *              id          :   xx // 用户id
             *              content     :   xx // 聊天内容
             *          },
             *          ...
             *      ]
             * }
             */
            var html = '';
            var selfID = $('#userid').val();
            var selfHead = $('#userhead').val();
            var selfName = $('#username').val();

            $(data.data).each(function (index,value) {
                // 判断当前是否为自己的聊天历史
                if(value['id'] == selfID)
                {
                    html += '<div class="self-box">'+
                        '<img src="'+selfHead+'" class="self-im-head" />' +
                        '<div class="self-content-box">' +
                        '<p class="self-im-name">'+selfName+'</p>' +
                        '<p class="self-im-content">'+value.content+'</p>' +
                        '</div>'+
                        '</div>';
                }else{
                    html += '<div class="receive-box">'+
                        '<img src="'+$('.friend-list-' + value.id).find('img').attr('src')+'" class="receive-im-head" />' +
                        '<div class="receive-content-box">' +
                        '<p class="receive-im-name">'+$('.friend-list-' + value.id).find('span').text()+'</p>' +
                        '<p class="receive-im-content">'+value.content+'</p>' +
                        '</div>'+
                        '</div>';
                }
            });
            html += '';
            $('.show-content').append(html);
            break;
        case 'single_im': // 单对单聊天
            /*
             需要判断接收到的数据是否为当前聊天窗口，如果不是则在对应的选项卡上 +1
             */

            /*
             * 返回 的数据 格式为
             * {
             *      'type'  :   'single_im', // 数据类型
             *      'data'  :   {
             *              id          :   xx // 用户id
             *              content     :   xx // 聊天内容
             *          }
             * }
             */
            // 判断当前是否为自己接收到自己发送的数据，如果为自己发送的数据则不处理
            if(data.data.id !== $('#userid'))
            {
                var head = $('.friend-list-' + data.data.id).find('img').attr('src');
                var name = $('.friend-list-' + data.data.id).find('span').text();
                console.log('.friend-list-' + data.data.id);
                console.log($('.friend-list-' + data.data.id));
                console.log($('.friend-list-' + data.data.id).find('img'));
                var html = '<div class="receive-box">'+
                    '<img src="'+head+'" class="receive-im-head" />' +
                    '<div class="receive-content-box">' +
                    '<p class="receive-im-name">'+name+'</p>' +
                    '<p class="receive-im-content">'+data.data.content+'</p>' +
                    '</div>'+
                    '</div>';
                console.log(html);
                $('.show-content').append(html);
            }

            break;
    }

};

$(document).on('click','#send',function () {
    // 先判断是否有对应用户选择聊天窗口
    var select = $('.select');
    if (select.length == 0)
    {
        alert('place select im')
        return false;
    }

    var _this = $(this);
    // 聊天内容
    var content = _this.parents('.send-content').find('textarea').val();

    // 将用户名与用户ID 链接起来传输
    var self_username = $('#username').val();
    var self_id = $('#userid').val();

    // 获取对应用户
    var to_username = select.data('username');
    var to_id = select.data('id');

    // 组合数据
    var data = {
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
    websocket.send(JSON.stringify(data));
    // 需要将当前数据发送的数据显示到聊天窗口

    var html = '<div class="self-box">'+
        '<img src="'+$('#userhead').val()+'" class="self-im-head" />' +
        '<div class="self-content-box">' +
        '<p class="self-im-name">'+$('#username').val()+'</p>' +
        '<p class="self-im-content">'+content+'</p>' +
        '</div>'+
        '</div>';
    $('.show-content').append(html);

    // 清空输入框内数据
    $('textarea').val('');
});

websocket.onerror = function (evt, e) {
    console.log('Error occured: ' + evt.data);
};

websocket.onclose = function (evt) {
    console.log("Disconnected");
};



// 监听关闭当前选项
$(document).on('click','#close',function ()
{
    // 清除当前选项卡上的select
    $('.select').removeClass('select');
    // 清空聊天窗口的数据
    $('.show-content').find('div').remove();
})

// 监听当前页面是否关闭或者强制刷新
window.onbeforeunload = function()
{
    // 清空此次会话的聊天记录
    cleanChat();
    // 通知服务器关闭链接
    websocket.close()
};

// 清空此次会话的聊天记录
function cleanChat() {
    // 将用户名与用户ID 链接起来传输
    var self_username = $('#username').val();
    var self_id = $('#userid').val();

    // 清除指令
    var data = {
        type        :   'clean_chat',
        self        :   {
            username        :   self_username,
            id              :   self_id
        }
    };
    // 发出清除指令
    websocket.send(JSON.stringify(data));
}
