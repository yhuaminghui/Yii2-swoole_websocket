<?php

namespace app\models;

class User extends \yii\base\BaseObject implements \yii\web\IdentityInterface
{
    public $id;
    public $username;
    public $password;
    public $head;
    public $authKey;
    public $accessToken;

    public static $users = [
        '100' => [
            'id' => '100',
            'username' => 'admin',
            'password' => 'admin',
            'head'      =>  '/img/0.jpg',
            'authKey' => 'test100key',
            'accessToken' => '100-token',
        ],
        '101' => [
            'id' => '101',
            'username' => 'demo',
            'password' => 'demo',
            'head'      =>'/img/1.jpg',
            'authKey' => 'test101key',
            'accessToken' => '101-token',
        ],
        '102' => [
            'id' => '102',
            'username' => 'zhangsan',
            'password' => 'zhangsan',
            'head'      =>'/img/2.jpg',
            'authKey' => 'test102key',
            'accessToken' => '102-token',
        ],
        '103' => [
            'id' => '103',
            'username' => 'lisi',
            'password' => 'lisi',
            'head'      =>'/img/3.jpg',
            'authKey' => 'test103key',
            'accessToken' => '103-token',
        ],
        '104' => [
            'id' => '104',
            'username' => 'wangwu',
            'password' => 'wangwu',
            'head'      =>'/img/4.jpg',
            'authKey' => 'test104key',
            'accessToken' => '104-token',
        ],
        '105' => [
            'id' => '105',
            'username' => 'zhaoliu',
            'password' => 'zhaoliu',
            'head'      =>'/img/5.jpg',
            'authKey' => 'test105key',
            'accessToken' => '105-token',
        ],
    ];


    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return isset(self::$users[$id]) ? new static(self::$users[$id]) : null;
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        foreach (self::$users as $user) {
            if ($user['accessToken'] === $token) {
                return new static($user);
            }
        }

        return null;
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        foreach (self::$users as $user) {
            if (strcasecmp($user['username'], $username) === 0) {
                return new static($user);
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->authKey === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return $this->password === $password;
    }
}
