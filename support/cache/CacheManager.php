<?php
/**
 * File:        CacheManger.php
 * Author:      albert <albert@rocareer.com>
 * Created:     2025/5/16 21:22
 * Description: 适配 Radmin 应用
 *
 * Copyright [2014-2026] [https://rocareer.com]
 * Licensed under the Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */

namespace support\cache;
class CacheManager extends  \Webman\ThinkCache\CacheManager
{
    /**
     * @var string|null
     */
    protected ?string $namespace = '\\Webman\\ThinkCache\\driver\\';

}