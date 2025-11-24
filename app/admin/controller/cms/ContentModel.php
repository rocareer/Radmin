<?php

namespace app\admin\controller\cms;

use Throwable;
use think\facade\Db;
use ba\TableManager;
use app\common\library\Menu;
use app\admin\model\AdminRule;
use app\common\controller\Backend;

/**
 * 内容模型管理
 *
 */
class ContentModel extends Backend
{
    /**
     * ContentModel模型对象
     * @var object
     * @phpstan-var \app\admin\model\cms\ContentModel
     */
    protected object $model;

    protected string|array $preExcludeFields = ['update_time', 'create_time'];

    protected string|array $quickSearchField = ['id', 'name'];

    protected array $noNeedPermission = ['info'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\cms\ContentModel;
    }

    public function info(): void
    {
        $id = $this->request->input($this->model->getPk(), 0);
        if (!$id) {
            $info = $this->model->where('status', 1)->order('create_time desc')->find();
        } else {
            $info = $this->model->find($id);
        }
        $this->success('', [
            'info' => $info
        ]);
    }


    /**
     * 添加
     * @throws Throwable
     */
    public function add(): void
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (!$data) {
                $this->error(__('Parameter %s can not be empty', ['']));
            }

            $data = $this->excludeFields($data);
            if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                $data[$this->dataLimitField] = $this->member->id;
            }

            // 检查表是否已在数据库内存在
            $tables    = TableManager::getTableList();
            $tableName = TableManager::tableName($data['table']);
            if (array_key_exists($tableName, $tables)) {
                $this->error(__('Data table already exists!'));
            }

            $result = false;
            $this->model->startTrans();
            try {
                // 模型验证
                if ($this->modelValidate) {
                    $validate = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                    if (class_exists($validate)) {
                        $validate = new $validate;
                        if ($this->modelSceneValidate) $validate->scene('add');
                        $validate->check($data);
                    }
                }
                $result = $this->model->save($data);

                // 读取主表字段列表
                $fieldData = [];
                $fields    = TableManager::getTableColumns('cms_content');
                // 投稿字段
                $contributeFields = ['channel_id', 'title', 'title_style', 'images', 'content', 'description', 'tags', 'price', 'currency'];
                // 后台公共搜索字段
                $comSearch = ['id', 'channel_id', 'title', 'url', 'views', 'comments', 'likes', 'dislikes', 'price', 'weigh', 'currency', 'status', 'publish_time', 'create_time'];
                // 后台排序字段
                $sort = ['id', 'views', 'comments', 'likes', 'dislikes', 'weigh', 'price', 'publish_time', 'update_time', 'create_time'];
                // 后台发布排除
                $publishExclude = ['create_time', 'update_time', 'url', 'target'];
                // 后台显示字段
                $show            = ['id', 'channel_id', 'title', 'images', 'views', 'comments', 'likes', 'dislikes', 'price', 'weigh', 'status', 'publish_time', 'create_time'];
                $nowTime         = time();
                $autoIncrementId = Db::name('cms_content_model_field_config')->max('weigh');
                foreach ($fields as $field) {
                    $autoIncrementId++;
                    if ($field['COLUMN_COMMENT']) {
                        $field['COLUMN_COMMENT'] = explode(':', $field['COLUMN_COMMENT']);
                        $field['COLUMN_COMMENT'] = $field['COLUMN_COMMENT'][0];
                    }
                    $fieldData[] = [
                        'content_model_id'    => $this->model->id,
                        'title'               => $field['COLUMN_COMMENT'],
                        'name'                => $field['COLUMN_NAME'],
                        'frontend_filter'     => false,
                        'frontend_contribute' => in_array($field['COLUMN_NAME'], $contributeFields),
                        'backend_show'        => in_array($field['COLUMN_NAME'], $show),
                        'backend_com_search'  => in_array($field['COLUMN_NAME'], $comSearch),
                        'backend_sort'        => in_array($field['COLUMN_NAME'], $sort),
                        'backend_publish'     => !in_array($field['COLUMN_NAME'], $publishExclude),
                        'weigh'               => $autoIncrementId,
                        'create_time'         => $nowTime,
                    ];
                }
                // 将主表字段数据入库到字段配置表
                Db::name('cms_content_model_field_config')->insertAll($fieldData);

                // 创建菜单规则
                $name  = 'cms/content/' . TableManager::tableName($data['table'], false);
                $pMenu = AdminRule::where('name', 'cms/content')->value('id');
                Menu::create([
                    [
                        'type'      => 'menu',
                        'title'     => $data['name'] . '管理',
                        'name'      => $name,
                        'path'      => $name,
                        'icon'      => 'fa fa-circle-o',
                        'menu_type' => 'tab',
                        'component' => '/src/views/backend/cms/content/index.vue',
                        'keepalive' => '0',
                        'pid'       => $pMenu ? $pMenu : 0,
                        'weigh'     => 1,
                        'children'  => [
                            ['type' => 'button', 'title' => '查看', 'name' => $name . '/index'],
                            ['type' => 'button', 'title' => '添加', 'name' => $name . '/add'],
                            ['type' => 'button', 'title' => '编辑', 'name' => $name . '/edit'],
                            ['type' => 'button', 'title' => '删除', 'name' => $name . '/del'],
                        ],
                    ]
                ]);

                // 创建对应数据表
                $sql = <<<EOT
CREATE TABLE `{$tableName}` (
    `id` int(10) UNSIGNED NOT NULL COMMENT 'ID(同主表)' ,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT="cms{$data['name']}表";
EOT;
                Db::execute($sql);
                $this->model->commit();
            } catch (Throwable $e) {
                $this->model->rollback();
                $this->error($e->getMessage());
            }
            if ($result !== false) {
                $this->success(__('Added successfully'));
            } else {
                $this->error(__('No rows were added'));
            }
        }

        $this->error(__('Parameter error'));
    }

    /**
     * 编辑
     * @throws Throwable
     */
    public function edit(): void
    {
        $id  = $this->request->input($this->model->getPk());
        $row = $this->model->find($id);
        if (!$row) {
            $this->error(__('Record not found'));
        }

        $dataLimitAdminIds = $this->getDataLimitAdminIds();
        if ($dataLimitAdminIds && !in_array($row[$this->dataLimitField], $dataLimitAdminIds)) {
            $this->error(__('You have no permission'));
        }

        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (!$data) {
                $this->error(__('Parameter %s can not be empty', ['']));
            }

            if (!array_diff_key($data, array_flip(['id', 'status']))) {
                $row->save($data);
                $this->success(__('Update successful'));
            }

            $tableName = TableManager::tableName($data['table']);
            if ($data['table'] != $row['table']) {
                // 检查表是否已在数据库内存在
                $tables = TableManager::getTableList();
                if (array_key_exists($tableName, $tables)) {
                    $this->error(__('Data table already exists!'));
                }
            }

            $data   = $this->excludeFields($data);
            $result = false;
            $this->model->startTrans();
            try {
                if ($data['table'] != $row['table']) {
                    $oldTableName = TableManager::tableName($row['table']);
                    $sql          = "ALTER TABLE `$oldTableName` RENAME TO `$tableName`";
                    Db::execute($sql);

                    // 修改菜单规则
                    $originalName = 'cms/content/' . TableManager::tableName($row['table'], false);
                    $newName      = 'cms/content/' . TableManager::tableName($data['table'], false);
                    Db::name('menu_rule')->where('name', $originalName)->update(['name' => $newName]);
                    $menuButtonNames = ['/index', '/add', '/edit', '/del'];
                    foreach ($menuButtonNames as $buttonName) {
                        Db::name('menu_rule')->where('name', $originalName . $buttonName)->update([
                            'name' => $newName . $buttonName,
                        ]);
                    }
                }

                // 模型验证
                if ($this->modelValidate) {
                    $validate = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                    if (class_exists($validate)) {
                        $validate = new $validate;
                        if ($this->modelSceneValidate) $validate->scene('edit');
                        $validate->check($data);
                    }
                }
                $result = $row->save($data);

                $this->model->commit();
            } catch (Throwable $e) {
                $this->model->rollback();
                $this->error($e->getMessage());
            }
            if ($result !== false) {
                $this->success(__('Update successful'));
            } else {
                $this->error(__('No rows updated'));
            }

        }

        $this->success('', [
            'row' => $row
        ]);
    }

    /**
     * 删除
     * @param array $ids
     * @throws Throwable
     */
    public function del(array $ids = []): void
    {
        if (!$this->request->isDelete() || !$ids) {
            $this->error(__('Parameter error'));
        }

        $dataLimitAdminIds = $this->getDataLimitAdminIds();
        if ($dataLimitAdminIds) {
            $this->model->where($this->dataLimitField, 'in', $dataLimitAdminIds);
        }

        $pk    = $this->model->getPk();
        $data  = $this->model->where($pk, 'in', $ids)->select();
        $count = 0;
        $this->model->startTrans();
        try {
            foreach ($data as $v) {
                // 删除字段配置
                Db::name('cms_content_model_field_config')->where('content_model_id', $v->id)->delete();
                // 删除数据表
                $tableName = TableManager::tableName($v->table);
                $sql       = "DROP TABLE `$tableName`";
                Db::execute($sql);

                // 删除菜单规则
                Menu::delete('cms/content/' . TableManager::tableName($v->table, false), true);

                $count += $v->delete();
            }
            $this->model->commit();
        } catch (Throwable $e) {
            $this->model->rollback();
            $this->error($e->getMessage());
        }
        if ($count) {
            $this->success(__('Deleted successfully'));
        } else {
            $this->error(__('No rows were deleted'));
        }
    }
}