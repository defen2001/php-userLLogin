用户留言板
===============


> 该demo使用了ThinkPHP5的的框架，所以运行环境要求PHP5.4以上。

详细开发文档参考 [ThinkPHP5完全开发手册](http://www.kancloud.cn/manual/thinkphp5)

## 目录结构

核心目录结构如下：

~~~
www  WEB部署目录（或者子目录）
├─application           应用目录
│  ├─index       
│  │  ├─controller
│  │  ├  ├─Index.php
│  │  ├  └─User.php
│  │  ├─model
│  │  ├  ├─Message.php
│  │  ├  └─User.php
│  │  └─view
│  │     ├─index
│  │     ├  ├─messagelist.html
│  │     ├  ├─postmess.html
│  │     └─user
│  │        ├─lpgon.html
│  │        └─register.html
│─public        
│  ├─static
│  │  ├─bootstrap.min.css
│  │  └─mystyle1.css



~~~
## 核心业务代码
Index.php

    //检测登录
    private function checkLogin()
    {
        if (!session('user.userId')) {
            $this->error('请登录', 'User/login');
        }
    }

    //留言列表
    public function index()
    {
        $list = Db::view('user', 'user_id,username')
            ->view('message', ['message_id', 'content', 'created_at'],
                'message.user_id=user.user_id')
            ->order('message_id desc')
            ->paginate(5);
        $this->assign('list', $list);
        $this->assign('count', count($list));
        return $this->fetch("messagelist");
    }

    //发表留言
    public function post()
    {
        $this->checkLogin();
        return $this->fetch("postmess");
    }

    //留言处理
    public function do_post()
    {
        $this->checkLogin();
        $content = input('post.content');
        if (empty($content)) {
            $this->error('留言内容不能为空');
        }
        if (mb_strlen($content, 'utf-8') > 100) {
            $this->error('留言内容最多100字');
        }
        $userId = session('user.userId');          # get by session
        $data = array(
            'content' => $content,
            'created_at' => time(),
            'user_id' => $userId
        );

        if ($result = Message::create($data)) {                   # 插入信息
            $this->success('留言成功', 'index/index');
        } else {
            $this->error('留言失败');
        }
    }

    public function delete()
    {
        $id = input('id');
        if (empty($id)) {
            $this->error('缺少参数');
        }
        $this->checkLogin();
        $result = Message::where(array('message_id' => $id, 'user_id' => session('user.userId')))->delete();
        if (!$result) {
            $this->error('删除失败');
        }
        $this->success('删除成功', 'index');
    }

User.php

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
