<?php

namespace app\admin\controller\cms;

use Throwable;
use think\facade\Db;
use ba\TableManager;
use app\common\controller\Backend;

/**
 * 内容模型字段管理
 *
 */
class ContentModelFieldConfig extends Backend
{
    /**
     * ContentModelFieldConfig模型对象
     * @var object
     * @phpstan-var \app\admin\model\cms\ContentModelFieldConfig
     */
    protected object $model;

    protected string|array $defaultSortField = 'weigh,desc';

    protected string|array $preExcludeFields = ['id', 'update_time', 'create_time'];

    protected array $withJoinTable = ['cmsContentModel'];

    protected string|array $quickSearchField = ['id'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\cms\ContentModelFieldConfig;

        $this->request->filter('clean_xss');
    }

    /**
     * 查看
     * @throws Throwable
     */
    public function index(): void
    {
        // 如果是select则转发到select方法,若select未重写,其实还是继续执行index
        if ($this->request->input('select')) {
            $this->select();
        }

        list($where, $alias, $limit, $order) = $this->queryBuilder();
        $order['id'] = 'desc';
        $res         = $this->model
            ->withJoin($this->withJoinTable, $this->withJoinType)
            ->alias($alias)
            ->where($where)
            ->order($order)
            ->select();

        // 字段是否是主表的？
        $fields = TableManager::getTableColumns('cms_content', true);
        foreach ($res as $item) {
            $item->main_field = array_key_exists($item->name, $fields);
        }
        $res->visible(['cmsContentModel' => ['name']]);

        $this->success('', [
            'list'   => $res,
            'remark' => get_route_remark(),
        ]);
    }

    public static function buildDefault($defaultValue): string
    {
        $default = '';
        if ($defaultValue == 'null') {
            $default = 'DEFAULT NULL';
        } elseif ($defaultValue == 'empty string') {
            $default = "DEFAULT ''";
        } elseif ($defaultValue == '0') {
            $default = "DEFAULT '0'";
        } elseif ($defaultValue) {
            $default = " DEFAULT '{$defaultValue}'";
        }
        return $default;
    }

    public static function restoreDefault($defaultValue): string
    {
        $default = $defaultValue;
        if (is_null($defaultValue)) {
            $default = 'null';
        } else if ($defaultValue == '') {
            $default = "empty string";
        }
        return $default;
    }

    public static function buildDict($dict): string
    {
        if (!$dict) return '';
        $str      = '';
        $dictTemp = str_attr_to_array($dict);
        foreach ($dictTemp as $key => $item) {
            if (is_string($item)) $str .= $key . '=' . $item . ',';
        }
        return trim($str, ',');
    }

    public static function restoreDict($comment): string
    {
        $comment = explode(':', $comment);
        if (!isset($comment[1])) return '';
        return str_replace(',', "\n", $comment[1]);
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

            // 检查字段名称
            $fields = TableManager::getTableColumns('cms_content', true);
            if (array_key_exists($data['name'], $fields)) {
                $this->error('字段名已在主表内存在，请更换！');
            }

            // 建立字段
            $contentModel = Db::name('cms_content_model')
                ->where('id', $data['content_model_id'])
                ->find();
            if (!$contentModel) {
                $this->error('所选模型不存在！');
            }
            $contentModelFields = TableManager::getTableColumns($contentModel['table'], true);
            if (array_key_exists($data['name'], $contentModelFields)) {
                $this->error('字段名已在模型表内存在，请更换！');
            }

            $tableName = TableManager::tableName($contentModel['table']);
            $this->model->startTrans();
            try {
                $dictStr = self::buildDict($data['dict']);
                $title   = $dictStr ? $data['title'] . ':' . $dictStr : $data['title'];
                $default = self::buildDefault($data['default_value'] ?? '');
                $sql     = "ALTER TABLE `$tableName` ADD COLUMN {$data['name']} {$data['data_type']} $default COMMENT '$title';";
                Db::execute($sql);
                $this->model->commit();
            } catch (Throwable $e) {
                $this->model->rollback();
                $this->error($e->getMessage());
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

        // 主表字段信息
        $fields       = TableManager::getTableColumns('cms_content');
        $contentModel = Db::name('cms_content_model')
            ->where('id', $row->content_model_id)
            ->find();
        if (!$contentModel) {
            $this->error('所选模型不存在！');
        }
        // 字段是否是主表的？
        $row->main_field = array_key_exists($row->name, $fields);
        // 模型表字段信息
        $contentModelFields = TableManager::getTableColumns($contentModel['table']);
        $fieldInfo          = $contentModelFields[$row->name] ?? $fields[$row->name];

        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (!$data) {
                $this->error(__('Parameter %s can not be empty', ['']));
            }

            if (!$row->main_field && isset($data['name']) && isset($data['title']) && isset($data['data_type'])) {
                $default = self::buildDefault($data['default_value'] ?? '');
                if ($row->name != $data['name'] || $row->title != $data['title'] || $fieldInfo['COLUMN_TYPE'] != $data['data_type'] || $default != $fieldInfo['COLUMN_DEFAULT']) {
                    if ($row->name != $data['name'] && array_key_exists($data['name'], $contentModelFields)) {
                        $this->error('字段名已在模型表内存在，请更换！');
                    }

                    $tableName = TableManager::tableName($contentModel['table']);
                    $this->model->startTrans();
                    try {
                        $dictStr = self::buildDict($data['dict']);
                        $title   = $dictStr ? $data['title'] . ':' . $dictStr : $data['title'];
                        $sql     = "ALTER TABLE `$tableName` CHANGE {$row['name']} {$data['name']} {$data['data_type']} $default COMMENT '$title';";
                        Db::execute($sql);
                        $this->model->commit();
                    } catch (Throwable $e) {
                        $this->model->rollback();
                        $this->error($e->getMessage());
                    }
                }
            }

            $data   = $this->excludeFields($data);
            $result = false;
            $this->model->startTrans();
            try {
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

        $row->dict          = self::restoreDict($fieldInfo['COLUMN_COMMENT']);
        $row->data_type     = $fieldInfo['COLUMN_TYPE'];
        $row->default_value = self::restoreDefault($fieldInfo['COLUMN_DEFAULT']);
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
        if ($this->request->method() != 'DELETE' || !$ids) {
            $this->error(__('Parameter error'));
        }

        $dataLimitAdminIds = $this->getDataLimitAdminIds();
        if ($dataLimitAdminIds) {
            $this->model->where($this->dataLimitField, 'in', $dataLimitAdminIds);
        }

        $pk     = $this->model->getPk();
        $fields = TableManager::getTableColumns('cms_content');
        $data   = $this->model->where($pk, 'in', $ids)->select();
        $count  = 0;
        $this->model->startTrans();
        try {
            $contentModels = [];
            foreach ($data as $v) {
                if (!array_key_exists($v->name, $fields)) {
                    // 删除字段
                    if (!isset($contentModels[$v->content_model_id])) {
                        $contentModels[$v->content_model_id] = Db::name('cms_content_model')
                            ->where('id', $v->content_model_id)
                            ->find();
                    }
                    if ($contentModels[$v->content_model_id]) {
                        $tableName = TableManager::tableName($contentModels[$v->content_model_id]['table']);
                        $sql       = "ALTER TABLE `$tableName` DROP COLUMN `$v->name`";
                        Db::execute($sql);
                    }
                    $count += $v->delete();
                }
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