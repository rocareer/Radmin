<?php

namespace support\member;

use plugin\admin\app\model\Role;
use support\Container;

/**
 * 成员上下文管理器
 *
 * 用于管理成员实例的生命周期
 */
class Context
{
    protected array $data = [];
    /**
     * @var mixed|null
     */
    public mixed $role;
    public mixed $model;
    public mixed $state;
    public mixed $authenticator;
    public mixed $service;

    public function set(string $key, $value): void
    {
        $this->data[$key] = $value;
    }

    public function get(string $key)
    {
        return $this->data[$key] ?? null;
    }

    public function clear(): void
    {
        $this->data          = []; // 清空数据
        $this->role          = null;
        $this->service       = null;
        $this->model         = null;
        $this->state         = null;
        $this->authenticator = null;
    }

    public function role($role)
    {
        $this->role          = $role;
        $this->model         = Container::get('member.model');
        $this->service       = Container::get('member.service');
        $this->authenticator = Container::get('member.authenticator');
        $this->state         = Container::get('member.state');
    }
}