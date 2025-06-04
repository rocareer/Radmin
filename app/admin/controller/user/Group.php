<?php
/**
 * File:        Group.php
 * Author:      albert <albert@rocareer.com>
 * Created:     2025/5/22 09:19
 * Description:
 *
 * Copyright [2014-2026] [https://rocareer.com]
 * Licensed under the Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */
namespace app\admin\controller\user;

use app\admin\model\UserGroup;
use app\admin\model\UserRule;
use app\common\controller\Backend;
use extend\ba\Tree;
use extend\ra\SystemUtil;
use support\Response;
use Throwable;

class Group extends Backend
{
    /**
     * @var object
     * @phpstan-var UserGroup
     */
    protected object $model;
    /**
     * @var Tree
     */
    protected Tree $tree; // 树状结构
    // 排除字段
    protected string|array $preExcludeFields = ['update_time', 'create_time'];

    protected string|array $quickSearchField = 'name';
    protected bool         $assembleTree= false;
    protected mixed          $keyword;
    protected mixed          $initValue;

    public function initialize():void
    {
        parent::initialize();
        $this->model = new UserGroup();
        $this->tree  = Tree::instance();
        $isTree          = $this->request->input('isTree/b', false);
        $this->initValue = $this->request->input("initValue/a", []);

        $this->initValue = array_filter($this->initValue);
        $this->keyword   = $this->request->input("quickSearch", '');

        // 有初始化值时不组装树状（初始化出来的值更好看）
        $this->assembleTree = $isTree && !$this->initValue;
    }


    public function index(): ?Response
    {
        if ($this->request->input('select')) {
            return $this->select();
        }

        list($where, $alias, $limit, $order) = $this->queryBuilder();

        $res = $this->model
            ->field($this->indexField)
            ->withJoin($this->withJoinTable, $this->withJoinType)
            ->alias($alias)
            ->where($where)
            ->order($order)
            ->paginate($limit);

        return $this->success('', [
            'list'   => $res->items(),
            'total'  => $res->total(),
            'remark' => SystemUtil::get_route_remark(),
        ]);
    }
    /**
     * 添加
     * @throws Throwable
     */
    public function add(): Response
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (!$data) {
                return $this->error(__('Parameter %s can not be empty', ['']));
            }

            $data = $this->excludeFields($data);
            $data = $this->handleRules($data);

            $result = false;
            $this->model->startTrans();
            try {
                // 模型验证
                if ($this->modelValidate) {
                    $validate = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                    if (class_exists($validate)) {
                        $validate = new $validate();
                        $validate->scene('add')->check($data);
                    }
                }
                $result = $this->model->save($data);
                $this->model->commit();
            } catch (Throwable $e) {
                $this->model->rollback();
                return $this->error($e->getMessage());
            }
            if ($result !== false) {
                return $this->success(__('Added successfully'));
            } else {
                return $this->error(__('No rows were added'));
            }
        }

        return $this->error(__('Parameter error'));
    }

    /**
     * 编辑
     * @throws Throwable
     */
    public function edit(): Response
    {
        $pk  = $this->model->getPk();
        $id  = $this->request->input($pk);
        $row = $this->model->find($id);
        if (!$row) {
            return $this->error(__('Record not found'));
        }

        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (!$data) {
                return $this->error(__('Parameter %s can not be empty', ['']));
            }

            $data = $this->excludeFields($data);
            $data = $this->handleRules($data);

            $result = false;
            $this->model->startTrans();
            try {
                // 模型验证
                if ($this->modelValidate) {
                    $validate = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                    if (class_exists($validate)) {
                        $validate = new $validate();
                        $validate->scene('edit')->check($data);
                    }
                }
                $result = $row->save($data);
                $this->model->commit();
            } catch (Throwable $e) {
                $this->model->rollback();
                return $this->error($e->getMessage());
            }
            if ($result !== false) {
                return $this->success(__('Update successful'));
            } else {
                return $this->error(__('No rows updated'));
            }
        }

        // 读取所有pid，全部从节点数组移除，父级选择状态由子级决定
        $pidArr = UserRule::field('pid')
            ->distinct()
            ->where('id', 'in', $row->rules)
            ->select()
            ->toArray();
        $rules  = $row->rules ? explode(',', $row->rules) : [];
        foreach ($pidArr as $item) {
            $ruKey = array_search($item['pid'], $rules);
            if ($ruKey !== false) {
                unset($rules[$ruKey]);
            }
        }
        $row          = $row->toArray();
        $row['rules'] = array_values($rules);
        return $this->success('', [
            'row' => $row
        ]);
    }

    /**
     * 权限规则入库前处理
     * @param array $data 接受到的数据
     * @return array
     * @throws Throwable
     */
    private function handleRules(array &$data): array
    {
        if (is_array($data['rules']) && $data['rules']) {
            $rules = UserRule::select();
            $super = true;
            foreach ($rules as $rule) {
                if (!in_array($rule['id'], $data['rules'])) {
                    $super = false;
                }
            }

            if ($super) {
                $data['rules'] = '*';
            } else {
                $data['rules'] = implode(',', $data['rules']);
            }
        } else {
            unset($data['rules']);
        }
        return $data;
    }
    private function getGroups(array $where = []): array
    {
        $pk      = $this->model->getPk();
        $initKey = $this->request->input("initKey", $pk);

        if ($this->initValue) {
            $where[] = [$initKey, 'in', $this->initValue];
        }

        $data = $this->model->where($where)->select()->toArray();


        // 如果要求树状，此处先组装好 children
        return $data;
    }

    /**
     * 远程下拉
     * @throws Throwable
     */
    public function select()
    {
        $data = $this->getGroups([['status', '=', 1]]);

        return $this->success('', [
            'options' => $data
        ]);
    }

}