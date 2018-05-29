<?php
use yii\helpers\Html;

?>
<?= Html::cssFile('/css/im_index.css?' . time()) ?>
<?= Html::jsFile('http://cdn.static.runoob.com/libs/jquery/1.10.2/jquery.min.js') ?>
<div class="im-box">
    <div class="left-user">
        <?php foreach ($friend as $value): ?>
        <div class="friend-list friend-list-<?= Html::encode($value['id'])?>" data-username="<?= Html::encode($value['username'])?>" data-id="<?= Html::encode($value['id'])?>">
            <img src="<?=Html::encode($value['head'])?>" alt="">
            <span><?= Html::encode($value['username'])?></span>
        </div>
        <?php endforeach;?>
    </div>
    <div class="right-content">
        <div class="show-content">

        </div>
        <div class="send-content">
            <textarea name="" id="" cols="30" rows="10"></textarea>

            <button type="button" class="btn btn-success" id="send">发送</button>
            <button type="button" class="btn btn-danger" id="close">关闭</button>
        </div>
    </div>
</div>
<?= Html::fileInput('',Html::encode($user->username),['type'=>'hidden','id'=>'username']) ?>
<?= Html::fileInput('',Html::encode($user->id),['type'=>'hidden','id'=>'userid'])?>
<?= Html::fileInput('',Html::encode($user->head),['type'=>'hidden','id'=>'userhead'])?>

<?= Html::jsFile('/js/im_index.js?' . time()) ?>


