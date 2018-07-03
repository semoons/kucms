<?php
namespace app\manage\controller;

use think\Url;
use think\Request;
use core\manage\logic\DatabaseLogic;

class DataBase extends Base
{

    /**
     * 数据表
     *
     * @return string
     */
    public function index()
    {
        $this->site_title = '备份数据';
        
        // 数据表
        $list = DatabaseLogic::instance()->getTableList();
        $this->assign('list', $list);
        
        return $this->fetch();
    }

    /**
     * 备份表
     *
     * @return mixed
     */
    public function bakup()
    {
        $res = DatabaseLogic::instance()->addBakup($this->user_id);
        if ($res['code'] == 1) {
            return $this->success($res['msg'], Url::build('database/bakupLog'));
        } else {
            return $this->error($res['msg']);
        }
    }

    /**
     * 优化表
     *
     * @return mixed
     */
    public function optimize()
    {
        DatabaseLogic::instance()->optimize();
        
        return $this->success('优化表成功', Url::build('database/index'));
    }

    /**
     * 修复表
     *
     * @return mixed
     */
    public function repair()
    {
        DatabaseLogic::instance()->repair();
        
        return $this->success('修复表成功', Url::build('database/index'));
    }

    /**
     * 备份记录
     *
     * @return string
     */
    public function bakupLog()
    {
        $this->site_title = '备份记录';
        
        // 备份记录
        $list = DatabaseLogic::instance()->getList();
        $this->assign('list', $list);
        
        return $this->fetch();
    }

    /**
     * 删除记录
     */
    public function delBakup(Request $request)
    {
        $bakup_id = $request->param('bakup_id');
        if (empty($bakup_id)) {
            return $this->error('备份ID为空');
        }
        
        // 删除备份
        DatabaseLogic::instance()->delBakup($bakup_id);
        
        return $this->success('删除备份记录成功', Url::build('database/bakupLog'));
    }
}