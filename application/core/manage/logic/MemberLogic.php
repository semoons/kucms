<?php
namespace core\manage\logic;

use cms\Logic;
use cms\Common;
use newday\common\Format;

class MemberLogic extends Logic
{

    /**
     * 用户缓存
     *
     * @var unknown
     */
    protected static $users;

    /**
     * 正常登录
     *
     * @var unknown
     */
    const TYPE_USER_LOGIN = 1;

    /**
     * 用户不存在
     *
     * @var unknown
     */
    const TYPE_USER_EMPTY = 0;

    /**
     * 用户未启用
     *
     * @var unknown
     */
    const TYPE_USER_UNUSE = - 1;

    /**
     * 用户被禁用
     *
     * @var unknown
     */
    const TYPE_USER_BANED = - 2;

    /**
     * 未分配群组
     *
     * @var unknown
     */
    const TYPE_GROUP_EMPTY = - 3;

    /**
     * 群组未启用
     *
     * @var unknown
     */
    const TYPE_GROUP_BANNED = - 4;

    /**
     * 超级用户
     *
     * @param number $user_id            
     * @return number
     */
    public function isAdmin($user_id)
    {
        return $user_id == 1;
    }

    /**
     * 添加用户
     *
     * @param array $data            
     * @return number
     */
    public function addMember($data)
    {
        $data['user_passwd'] = $this->encryptPasswd($data['user_passwd']);
        unset($data['re_passwd']);
        
        return $this->model->add($data);
    }

    /**
     * 修改用户
     *
     * @param array $data            
     * @param array $user_id            
     * @return number
     */
    public function saveMember($data, $user_id)
    {
        // 密码加密
        if (empty($data['user_passwd'])) {
            unset($data['user_passwd']);
        } else {
            $data['user_passwd'] = $this->encryptPasswd($data['user_passwd']);
        }
        
        // 重复密码
        if (isset($data['re_passwd'])) {
            unset($data['re_passwd']);
        }
        
        return $this->saveUser($user_id, $data);
    }

    /**
     * 获取用户
     *
     * @param number $user_id            
     * @return array
     */
    public function getUser($user_id)
    {
        if (empty(self::$users[$user_id])) {
            self::$users[$user_id] = $this->model->get($user_id);
        }
        return self::$users[$user_id];
    }

    /**
     * 保存用户
     *
     * @param number $user_id            
     * @param array $data            
     * @return number
     */
    public function saveUser($user_id, $data)
    {
        // 清除缓存
        unset(self::$users[$user_id]);
        
        return $this->model->save($data, $user_id);
    }

    /**
     * 用户菜单
     *
     * @param number $user_id            
     * @return array
     */
    public function getUserMenu($user_id)
    {
        $user = $this->getUser($user_id);
        return MemberGroupLogic::instance()->getGroupMenu($user['group_id']);
    }

    /**
     * 验证登录
     *
     * @param string $user_name            
     * @param string $user_pass            
     * @return array
     */
    public function checkLogin($user_name, $user_pass)
    {
        $map = [
            'user_name' => $user_name,
            'user_passwd' => $this->encryptPasswd($user_pass)
        ];
        $user = $this->model->where($map)->find();
        
        // 用户状态
        if (empty($user)) {
            return Format::formatResult(self::TYPE_USER_EMPTY, '账号或者密码错误');
        } elseif ($user['user_status'] == 0) {
            return Format::formatResult(self::TYPE_USER_UNUSE, '未启用的账号');
        } elseif ($user['user_status'] == - 1) {
            return Format::formatResult(self::TYPE_USER_BANED, '该账号已经被禁用');
        }
        
        // 群组状态
        $group = MemberGroupLogic::model()->get($user['id']);
        if (empty($group)) {
            return Format::formatResult(self::TYPE_GROUP_EMPTY, '尚未分配用户群组');
        } elseif ($group['group_status'] == 0) {
            return Format::formatResult(self::TYPE_GROUP_BANNED, '用户所在群组被禁止登录');
        }
        
        // 登录日志
        $this->logLogin($user['id']);
        
        return Format::formatResult(self::TYPE_USER_LOGIN, '用户登录成功', [
            'user' => $user,
            'group' => $group
        ]);
    }

    /**
     * 登录日志
     *
     * @param number $user_id            
     */
    public function logLogin($user_id)
    {
        $data = [
            'login_count' => [
                'exp',
                'login_count + 1'
            ],
            'login_time' => time(),
            'login_ip' => Common::getIp()
        ];
        $this->saveUser($user_id, $data);
        
        // 记录日志
        MemberLogLogic::instance()->addLog($user_id);
    }

    /**
     * 加密密码
     *
     * @param string $passwd            
     * @return string
     */
    public function encryptPasswd($passwd)
    {
        return md5(gzcompress($passwd) . base64_decode($passwd));
    }

    /**
     * 用户下拉选择
     *
     * @return array
     */
    public function getUserSelect()
    {
        $list = $this->model->select();
        $user_select = [];
        foreach ($list as $vo) {
            $user_select[$vo['id']] = [
                'name' => $vo['user_name'] . '(' . $vo['user_nick'] . ')',
                'value' => $vo['id']
            ];
        }
        return $user_select;
    }

    /**
     * 用户状态
     *
     * @return array
     */
    public function userStatus()
    {
        return [
            [
                'name' => '启用',
                'value' => 1
            ],
            [
                'name' => '禁用',
                'value' => 0
            ]
        ];
    }
}