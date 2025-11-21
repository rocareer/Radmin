<?php
/** @noinspection PhpUndefinedFieldInspection */

/** @noinspection PhpPossiblePolymorphicInvocationInspection */

namespace app\middleware;

use Exception;
use support\Request;
use support\member\RoleManager;
use Webman\Event\Event;

class RequestMiddleWare implements MiddlewareInterface
{
    /**
     * @throws Exception
     */
    public function process(Request $request, callable $handler)
    {
        // 检测并设置角色
        $this->detectAndSetRole($request);
        
        // 预提取token（使用缓存机制，避免重复提取）
        $request->token();
        
        // 穿越洋葱
        return $handler($request);
    }
    
    /**
     * 检测并设置请求角色
     */
    protected function detectAndSetRole(Request $request): void
    {
        $roleManager = RoleManager::getInstance();
        
        // 优先使用请求中指定的角色
        $requestRole = $request->input('x-role');
        if ($requestRole && $roleManager->validateRole($requestRole)) {
            $request->role = $requestRole;
            return;
        }
        
        // 如果有Token，尝试从Token中获取角色
        $token = $request->token();
        if ($token) {
            try {
                $payload = \support\token\Token::verify($token);
                if (isset($payload->role) && $roleManager->validateRole($payload->role)) {
                    $request->role = $payload->role;
                    
                    // 记录Token角色检测日志
                    Event::emit('request.role_detected', [
                        'role' => $request->role,
                        'request_path' => $request->path(),
                        'method' => $request->method(),
                        'source' => 'token'
                    ]);
                    return;
                }
            } catch (\Throwable $e) {
                // Token验证失败，继续使用路径检测
            }
        }
        
        // 使用角色管理器基于路径检测
        $request->role = $roleManager->detectRoleByRequest($request);
        
        // 记录路径角色检测日志
        Event::emit('request.role_detected', [
            'role' => $request->role,
            'request_path' => $request->path(),
            'method' => $request->method(),
            'source' => 'path'
        ]);
    }

}
