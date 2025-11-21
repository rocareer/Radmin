<?php

namespace support\member;

use support\Container;

/**
 * 依赖注入特性
 * 提供统一的依赖注入功能
 */
trait DependencyInjectionTrait
{
    /**
     * 创建模型实例
     */
    protected function createModel(string $role): object
    {
        return Container::get('member.model.factory')->createModel($role);
    }
    
    /**
     * 创建认证器实例
     */
    protected function createAuthenticator(string $role): object
    {
        return Container::get('member.authenticator.factory')->createAuthenticator($role);
    }
    
    /**
     * 创建状态管理器实例
     */
    protected function createState(string $role): object
    {
        return Container::get('member.state.factory')->createState($role);
    }
    
    /**
     * 创建服务实例
     */
    protected function createService(string $role): object
    {
        return Container::get('member.service.factory')->createService($role);
    }
}