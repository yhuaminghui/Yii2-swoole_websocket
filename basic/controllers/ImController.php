<?php
/**
 * Created by PhpStorm.
 * User: 化明辉
 * Date: 2018/5/24
 * Time: 14:12
 */

namespace app\controllers;


use app\models\User;
use yii\web\Controller;

class ImController extends Controller
{
    public function actionIndex()
    {
        if(\Yii::$app->user->isGuest)
        {
            return $this->redirect(['site/login']);
        }else{
            // 获取当前用户
            $user = \Yii::$app->user;
            $userInfo = $user->identity;
            $allUserInfo = User::$users;

            $friend = [];

            foreach ($allUserInfo as $value) {
                if($value['id'] != $userInfo->id)
                {
                    $friend[] = $value;
                }
            }

            return $this->render('index',[
                'user'      =>  $user->identity,
                'friend'    =>  $friend
            ]);
        }



    }
}