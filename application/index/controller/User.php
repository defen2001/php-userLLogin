<?php

namespace app\index\controller;

use think\Controller;
use app\index\model\User as UserModel;

class User extends Controller
{
    public function register()
    {
        return $this->fetch('register');
    }

    public function doregister()
    {
        $username = input('post.username');
        $password = input('post.password');
        $repassword = input('post.repassword');

        if (empty($username)) {
            $this->error('用户名不能为空');
        }
        if (empty($password)) {
            $this->error('密码不能为空');
        }
        if (empty($repassword)) {
            $this->error('确认密码错误');
        }
        //检测用户是否已注册
        $user = UserModel::getByUsername($username);
        if (!empty($user)) {
            $this->error('用户名已存在');
        }
        $data = array(
            'username' => $username,
            'password' => md5($password),
            'created_at' => time()
        );
        if ($result = UserModel::create($data)) {
            $this->success('注册成功，请登录', 'login');
        } else {
            $this->error('注册失败');
        }
    }

    //用户登录
    public function login()
    {
        return $this->fetch();
    }

    //登录处理
    public function dologin()
    {
        $username = input('post.username');
        $password = input('post.password');
        $user = UserModel::getByUsername($username);
        if (empty($user) || $user['password'] != md5($password)) {
            $this->error('账号或密码错误');
        }
        //写入session
        session('user.userId', $user['user_id']);
        session('user.username', $user['username']);
        //跳转首页
        $this->redirect('Index/index');
    }

    //退出登录
    public function logout()
    {
        if (!session('user.userId')) {
            $this->error('请登录');
        }
        session_destroy();
        $this->success('退出登录成功', 'Index/index');
    }
}