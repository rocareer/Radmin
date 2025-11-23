<?php
/**
 * File:        20250511121333_radmin102.php
 * Author:      albert <albert@rocareer.com>
 * Created:     2025/5/11 22:52
 * Description:
 *
 * Copyright [2014-2026] [https://rocareer.com]
 * Licensed under the Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */

use Phinx\Migration\AbstractMigration;
use app\admin\model\Config;

class Radmin102 extends AbstractMigration
{
    /**
     * @throws Throwable
     */
    public function up()
    {

        // 删除 salt 字段
        $this->deleteSalt();

    }

    /**
     * 为 admin 和 user 表删除 salt 字段
     * @return   void
     * Author:   albert <albert@rocareer.com>
     * Time:     2025/5/21 04:45
     */
    public function deleteSalt(): void
    {

        $this->table(getDbPrefix().'admin')->removeColumn('salt')->save();
        $this->table(getDbPrefix().'user')->removeColumn('salt')->save();
    }



}
