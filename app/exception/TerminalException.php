<?php

namespace app\exception;

use support\StatusCode;
use Throwable;

class TerminalException extends Exception
{
    /**
     * 异常类型
     * @var string
     */
    protected $type = 'terminal';

    /**
     * 日志级别
     * @var string
     */
    protected $logLevel = 'warning';

    /**
     * 创建认证异常
     */
    public array $header = [
        'Content-Type' => 'text/event-stream',
    ];

}
