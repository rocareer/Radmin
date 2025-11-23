<?php
/**
 * File:        Rule.php
 * Author:      albert <albert@rocareer.com>
 * Created:     2025/11/23 14:17
 * Description:
 *
 * Copyright [2014-2026] [https://rocareer.com]
 * Licensed under the Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */

namespace support\member;

use support\member\interface\InterfaceRule;
use support\orm\Db;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use Throwable;

abstract class Rule implements InterfaceRule
{
    /**
     * @var string 用户角色
     */
    protected string $role = 'user';
    
    /**
     * @var array 默认配置
     */
    protected array $config = [
        'auth_group'        => 'user_group', // 用户组数据表名
        'auth_group_access' => '', // 用户-用户组关系表
        'auth_rule'         => 'user_rule', // 权限规则表
    ];
    
    /**
     * @var array 子节点
     */
    protected array $children = [];
    
    /**
     * @var object 用户模型实例
     */
    protected object $memberModel;
    
    public function __construct(string $role, object $memberModel)
    {
        $this->role = $role;
        $this->memberModel = $memberModel;
    }
    
    /**
     * 获取菜单规则列表
     * @access public
     * @param int $uid 用户ID
     * @return array
     * @throws Throwable
     */
    public function getMenus(?int $uid = null): array
    {
        $uid = $uid ?? $this->memberModel->id;
        $this->children = [];

        $originAuthRules = $this->getOriginAuthRules($uid);

        foreach ($originAuthRules as $rule) {
            $this->children[$rule['pid']][] = $rule;
        }

        // 没有根菜单规则
        if (!isset($this->children[0])) return [];

        return $this->getChildren($this->children[0]);
    }

    /**
     * 获得权限规则原始数据
     * @param int|null $uid 用户id
     * @return array
     * @throws Throwable
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getOriginAuthRules(?int $uid = null): array
    {
        $uid = $uid ?? $this->memberModel->id;
        $ids = $this->getRuleIds($uid);
        if (empty($ids)) return [];

        $where = [];
        $where[] = ['status', '=', '1'];
        // 如果没有 * 则只获取用户拥有的规则
        if (!in_array('*', $ids)) {
            $where[] = ['id', 'in', $ids];
        }
        $rules = Db::name($this->config['auth_rule'])
            ->withoutField(['remark', 'status', 'weigh', 'update_time', 'create_time'])
            ->where($where)
            ->order('weigh desc,id asc')
            ->select()
            ->toArray();
        foreach ($rules as $key => $rule) {
            if (!empty($rule['keepalive'])) {
                $rules[$key]['keepalive'] = $rule['name'];
            }
        }

        return $rules;
    }

    /**
     * 获取权限规则ids
     * @param int $uid
     * @return array
     * @throws Throwable
     */
    public function getRuleIds(?int $uid = null): array
    {
        $uid = $uid ?? $this->memberModel->id;
        // 用户的组别和规则ID
        $groups = $this->getGroups($uid);

        $ids = [];
        foreach ($groups as $g) {
            $ids = array_merge($ids, explode(',', trim($g['rules'], ',')));
        }
        return array_unique($ids);
    }

    /**
     * 获取用户所有分组和对应权限规则
     * @param int $uid
     * @return array
     * @throws Throwable
     */
    public function getGroups(?int $uid = null): array
    {
        $uid = $uid ?? $this->memberModel->id;

        $dbName = $this->config['auth_group_access'] ?: 'user';
        if ($this->config['auth_group_access']) {
            $userGroups = Db::name($dbName)
                ->alias('aga')
                ->join($this->config['auth_group'] . ' ag', 'aga.group_id = ag.id', 'LEFT')
                ->field('aga.uid,aga.group_id,ag.id,ag.pid,ag.name,ag.rules')
                ->where("aga.uid='$uid' and ag.status='1'")
                ->select()
                ->toArray();
        } else {
            $userGroups = Db::name($dbName)
                ->alias('u')
                ->join($this->config['auth_group'] . ' ag', 'u.group_id = ag.id', 'LEFT')
                ->field('u.id as uid,u.group_id,ag.id,ag.name,ag.rules')
                ->where("u.id='$uid' and ag.status='1'")
                ->select()
                ->toArray();
        }

        return $userGroups;
    }

    /**
     * 获取传递的菜单规则的子规则
     * @param array $rules 菜单规则
     * @return array
     */
    private function getChildren(array $rules): array
    {
        foreach ($rules as $key => $rule) {
            if (array_key_exists($rule['id'], $this->children)) {
                $rules[$key]['children'] = $this->getChildren($this->children[$rule['id']]);
            }
        }
        return $rules;
    }

    /**
     * 检查是否有某权限
     * @param string $name     菜单规则的 name，可以传递两个，以','号隔开
     * @param int    $uid      用户ID
     * @param string $relation 如果出现两个 name,是两个都通过(and)还是一个通过即可(or)
     * @param string $mode     如果不使用 url 则菜单规则name匹配到即通过
     * @return bool
     * @throws Throwable
     */
    public function check(string $name, ?int $uid = null, string $relation = 'or', string $mode = 'url'): bool
    {
        $uid = $uid ?? $this->memberModel->id;
        // 获取用户需要验证的所有有效规则列表
        $ruleList = $this->getRuleList($uid);
        if (in_array('*', $ruleList)) {
            return true;
        }

        if ($name) {
            $name = strtolower($name);
            if (str_contains($name, ',')) {
                $name = explode(',', $name);
            } else {
                $name = [$name];
            }
        }
        $list = []; //保存验证通过的规则名
        if ('url' == $mode) {
            $REQUEST = json_decode(strtolower(json_encode(request()->all(), JSON_UNESCAPED_UNICODE)), true);
        }
        foreach ($ruleList as $rule) {
            $query = preg_replace('/^.+\?/U', '', $rule);
            if ('url' == $mode && $query != $rule) {
                parse_str($query, $param); //解析规则中的param
                $intersect = array_intersect_assoc($REQUEST, $param);
                $rule = preg_replace('/\?.*$/U', '', $rule);
                if (in_array($rule, $name) && $intersect == $param) {
                    // 如果节点相符且url参数满足
                    $list[] = $rule;
                }
            } elseif (in_array($rule, $name)) {
                $list[] = $rule;
            }
        }
        if ('or' == $relation && !empty($list)) {
            return true;
        }

        $diff = array_diff($name, $list);
        if ('and' == $relation && empty($diff)) {
            return true;
        }

        return false;
    }

    /**
     * 获得权限规则列表
     * @param int|null $uid 用户id
     * @return array
     * @throws Throwable
     */
    public function getRuleList(?int $uid = null): array
    {
        $uid = $uid ?? $this->memberModel->id;
        // 读取用户规则节点
        $ids = $this->getRuleIds($uid);
        if (empty($ids)) return [];

        $originAuthRules = $this->getOriginAuthRules($uid);

        // 用户规则
        $rules = [];
        if (in_array('*', $ids)) {
            $rules[] = "*";
        }
        foreach ($originAuthRules as $rule) {
            $rules[$rule['id']] = strtolower($rule['name']);
        }
        return array_unique($rules);
    }
}