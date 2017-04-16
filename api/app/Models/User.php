<?php

namespace App\Models;


class User
{
    private $userType = 0;

    private $userInfo = array();

    private $uid = false;

    private $isLogin = 0;

    public function isLogin()
    {
        return $this->isLogin === 1;
    }

    public function setLogin($status = 0)
    {
        $status = intval($status) == 1 ? 1 : 0;
        $this->isLogin = $status;
    }

    /**
     * @param string|int $uid
     */
    public function setUid($uid)
    {
        $this->uid = $uid;
    }

    /**
     * @return string|int
     */
    public function getUid()
    {
        return $this->uid;
    }
    
    public function setUserType($userType)
    {
        $this->userType = $userType;
    }
    
    public function getUserType()
    {
        return $this->userType;
    }

    public function setUserInfo($userInfo = array())
    {
        $this->userInfo = $userInfo;
    }

    /**
     * @param string $name
     * @return array
     */
    public function getUserInfo($name='')
    {
        if ($name)
        {
            if (array_key_exists($name, $this->userInfo)) {
                return $this->userInfo[$name];
            }
            return null;
        }
        return $this->userInfo;
    }
}

