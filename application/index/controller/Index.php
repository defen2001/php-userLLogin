<?php

namespace app\index\controller;

use app\index\model\Message;
use app\index\model\User as UserModel;
use think\Controller;

//use think\Model;
use think\Db;

class Index extends Controller
{
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
}
