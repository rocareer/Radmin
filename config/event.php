<?php
/**
 * File:        event.php
 * Author:      albert <albert@rocareer.com>
 * Created:     2025/5/12 05:37
 * Description:
 *
 * Copyright [2014-2026] [https://rocareer.com]
 * Licensed under the Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */

use app\admin\model\data\Backup;
use app\admin\model\log\authentication\Admin;
use app\admin\model\log\data\Backup as BackupLog;
use app\admin\model\log\data\Restore as RestoreLog;
use support\member\State;

return [
    // 状态事件
    'state.checkStatus'   => [
        [State::class, 'checkStatus'],
    ],
    'state.updateLogin.*' => [
        [State::class, 'updateLoginState'],
    ],



];
