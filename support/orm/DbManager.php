<?php

declare (strict_types = 1);

namespace support\orm;

use MongoDB\Driver\Command;
use think\db\BaseQuery;
use think\db\ConnectionInterface;
use think\db\Query;
use Throwable;
use Webman\Context;
use Workerman\Coroutine\Pool;

/**
 * Class DbManager.
 *
 * @mixin BaseQuery
 * @mixin Query
 */
class DbManager extends \think\DbManager
{

}
