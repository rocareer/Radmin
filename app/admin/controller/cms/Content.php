<?php

namespace app\admin\controller\cms;

use extend\ba\TableManager;
use extend\ra\FileUtil;
use extend\ra\SystemUtil;
use modules\cms\library\Helper;
use support\member\Member;
use support\Response;
use Throwable;
use think\facade\Db;
use app\common\controller\Backend;
use app\admin\library\module\Server;
use app\admin\model\cms\Tags;

/**
 * 内容管理
 *
 */
class Content extends Backend
{
    /**
     * Content模型对象
     * @var object
     * @phpstan-var \app\admin\model\cms\Content
     */
    protected object $model;

    protected string|array $defaultSortField = 'weigh,desc';

    protected string|array $preExcludeFields = ['update_time', 'create_time'];

    protected array $withJoinTable = ['user', 'admin', 'cmsChannel'];

    protected string|array $quickSearchField = ['id', 'title'];

    // 免验权方法，CMS系统设计中本控制器将负责所有类型的“内容”管理，所以单独在对应方法内检查是否有对应“内容”的管理权限
    protected array $noNeedPermission = ['fields', 'index', 'add', 'edit', 'del', 'status'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\cms\Content;
        $this->request->filter('clean_xss');
    }

    public function fields(): Response
    {
        $id        = $this->request->input('id');
        $modelInfo = Db::name('cms_content_model')
            ->where(function ($query) use ($id) {
                if (is_numeric($id)) {
                    $query->where('id', $id);
                } else {
                    $query->where('table', $id);
                }
            })
            ->find();
        if (!$modelInfo) {
            return $this->error(__('The model cannot be found'));
        }

        $modelInfo['full_table'] = TableManager::tableName($modelInfo['table']);
        $fieldData               = Db::name('cms_content_model_field_config')
            ->where('content_model_id', $modelInfo['id'])
            ->where('status', 1)
            ->order(['weigh' => 'asc', 'id' => 'asc'])
            ->select()
            ->toArray();

        // 验查看权限 - 超管直接通过
        if (!Member::hasRole('super')) {
            $routePath = ($this->app->request->controlle() ?? '') . "/{$modelInfo['table']}/index";
            if (!Member::check($routePath)) {
                return $this->error(__('You have no permission'), [], 401);
            }
        }

        $fields             = [];
        $contentModelFields = TableManager::getTableColumns($modelInfo['table']);
        $modelFields        = TableManager::getTableColumns('cms_content', true);
        foreach ($fieldData as $field) {
            $mainField                           = array_key_exists($field['name'], $modelFields);
            $key                                 = $mainField ? $field['name'] : $modelInfo['full_table'] . '__' . $field['name'];
            $fields[$key]                        = $field;
            $fields[$key]['full_name']           = $key;
            $fields[$key]['main_field']          = $mainField;
            $fields[$key]['backend_column_attr'] = str_attr_to_array($field['backend_column_attr']);
            $fields[$key]['extend']              = str_attr_to_array($field['extend']);
            $fields[$key]['input_extend']        = str_attr_to_array($field['input_extend']);
            if (array_key_exists($field['name'], $contentModelFields)) {
                $fields[$key]['default'] = Helper::restoreDefault($contentModelFields[$field['name']]['COLUMN_DEFAULT'], $field['type']);
                $fields[$key]['content'] = Helper::restoreDict($contentModelFields[$field['name']]['COLUMN_COMMENT']);
            }
        }

        return $this->success('', [
            'fields'    => $fields,
            'modelInfo' => $modelInfo
        ]);
    }

    /**
     * 查看
     * @throws Throwable
     */
    public function index(): Response
    {
        // 如果是select则转发到select方法
        if ($this->request->input('select')) {
            $this->select();
        }
        $modelTable = $this->request->input('table', '');
        $modelInfo  = Db::name('cms_content_model')
            ->where('table', $modelTable)
            ->find();
        if (!$modelInfo) {
            return $this->error(__('The model cannot be found'));
        }

        // 超管直接通过
        if (!Member::hasRole('super')) {
            $routePath = ($this->app->request->controller ?? '') . "/$modelTable/{$this->request->action}";
            if (!Member::check($routePath)) {
                return $this->error(__('You have no permission'), [], 401);
            }
        }

        $fullModelFieldsConfig = [];
        $modelTableFullName    = TableManager::tableName($modelInfo['table']);
        $modelTableFullAsName  = $modelTableFullName . '__';
        $modelFieldsConfig     = Db::name('cms_content_model_field_config')
            ->where('content_model_id', $modelInfo['id'])
            ->where('type', '<>', '')
            ->where('status', 1)
            ->select()->toArray();
        foreach ($modelFieldsConfig as $item) {
            $fullModelFieldsConfig[$modelTableFullAsName . $item['name']] = $item;
        }

        list($where, $alias, $limit, $order) = $this->queryBuilder();
        $alias[$modelTableFullName] = $modelTableFullName;

        foreach ($where as $key => $item) {
            if (str_contains($item[0], $modelTableFullName . '__')) {
                $where[$key][0] = str_replace(['content.', $modelTableFullAsName], ['', $modelTableFullName . '.'], $item[0]);
            }
        }

        $res = $this->model
            ->field(true)
            ->tableField(true, $modelTableFullName, '', $modelTableFullAsName)
            ->join("$modelTableFullName", "$modelTableFullName.id=content.id", 'RIGHT')
            ->withJoin($this->withJoinTable, $this->withJoinType)
            ->alias($alias)
            ->where($where)
            ->order($order)
            ->visible(['cmsChannel' => ['name'], 'user' => ['nickname'], 'admin' => ['nickname']])
            ->paginate($limit)->each(function ($item) use ($modelTableFullAsName, $fullModelFieldsConfig) {
                foreach ($item->toArray() as $key => $field) {
                    if (str_contains($key, $modelTableFullAsName) && isset($fullModelFieldsConfig[$key])) {
                        $item[$key] = $this->model::modelDataOutput($item[$key], $fullModelFieldsConfig[$key]['type']);
                    }
                }
            });

        return $this->success('', [
            'list'      => $res->items(),
            'total'     => $res->total(),
            'remark'    => SystemUtil::get_route_remark(),
            'modelInfo' => $modelInfo,
        ]);
    }

    /**
     * 重写select
     * @throws Throwable
     */
    public function select(): Response
    {
        list($where, $alias, $limit, $order) = $this->queryBuilder();
        $res = $this->model
            ->field($this->indexField)
            ->withJoin($this->withJoinTable, $this->withJoinType)
            ->alias($alias)
            ->where($where)
            ->order($order)
            ->paginate($limit);

        return $this->success('', [
            'list'   => $res->items(),
            'total'  => $res->total(),
            'remark' => SystemUtil::get_route_remark(),
        ]);
    }

    /**
     * 添加
     * @throws Throwable
     */
    public function add(): Response
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (!$data) {
                return $this->error(__('Parameter %s can not be empty', ['']));
            }

            // 模型表数据
            $modelInfo = Db::name('cms_content_model')
                ->where('table', $data['content_model_table'])
                ->find();
            if (!$modelInfo) {
                return $this->error(__('The model cannot be found'));
            }

            $routePath = ($this->request->controller() ?? '') . '/' . $this->request->action;
            if (!Member::check($routePath)) {
                return $this->error(__('You have no permission'), [], 401);
            }

            $modelTableData     = [];
            $modelTableFullName = TableManager::tableName($modelInfo['table']);
            $modelFieldsConfig  = Db::name('cms_content_model_field_config')
                ->where('content_model_id', $modelInfo['id'])
                ->where('type', '<>', '')
                ->where('status', 1)
                ->column('type', 'name');
            foreach ($data as $key => $datum) {
                if (str_contains($key, $modelTableFullName . '__')) {
                    $fieldName                  = str_replace(['content.', $modelTableFullName . '__'], '', $key);
                    $modelTableData[$fieldName] = $this->model::setModelTableData($datum, $modelFieldsConfig[$fieldName] ?? '');
                }
            }

            $data = $this->excludeFields($data);
            if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                $data[$this->dataLimitField] = $this->member->id;
            }

            if (empty($data['publish_time']) && $data['status'] == 'normal') {
                $data['publish_time'] = time();
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

                // 自动新建标签
                if (isset($data['tags'])) {
                    $data['tags'] = Helper::autoCreateTags($data['tags']);
                    foreach ($data['tags'] as $tag) {
                        Tags::where('id', $tag)->inc('document_count')->save();
                    }
                }

                $result               = $this->model->save($data);
                $modelTableData['id'] = $this->model->id;
                Db::name($modelInfo['table'])->insert($modelTableData);

                // 用户动态
                if ($data['status'] == 'normal' && !empty($data['user_id'])) {
                    $interaction = Server::getIni(FileUtil::fsFit(root_path() . 'modules/interaction/'));
                    if ($interaction && $interaction['state'] == 1) {
                        $recentHtml = '发表了内容 <a href="/cms/info/' . $this->model->id . '">' . $data['title'] . '</a>';
                        \app\admin\model\user\Recent::create([
                            'user_id' => $data['user_id'],
                            'content' => $recentHtml,
                        ]);
                    }
                }

                $this->model->commit();
            } catch (Throwable $e) {
                $this->model->rollback();
                return $this->error($e->getMessage());
            }
            if ($result !== false) {
                return $this->success(__('Added successfully'));
            } else {
                return $this->error(__('No rows were added'));
            }
        }

        return $this->error(__('Parameter error'));
    }

    /**
     * 编辑
     * @throws Throwable
     */
    public function edit(): Response
    {
        $modelTable = $this->request->input('content_model_table', '');
        $modelInfo  = Db::name('cms_content_model')
            ->where('table', $modelTable)
            ->find();
        if (!$modelInfo) {
            return $this->error(__('The model cannot be found'));
        }

        $routePath = ($this->request->controller() ?? '') . '/' . $this->request->action;
        if (!Member::check($routePath)) {
            return $this->error(__('You have no permission'), [], 401);
        }

        $modelTableFullName = TableManager::tableName($modelInfo['table']);
        $modelFieldsConfig  = Db::name('cms_content_model_field_config')
            ->where('content_model_id', $modelInfo['id'])
            ->where('type', '<>', '')
            ->where('status', 1)
            ->column('type', 'name');

        $id  = $this->request->input($this->model->getPk());
        $row = $this->model->find($id);
        if (!$row) {
            return $this->error(__('Record not found'));
        }

        $dataLimitAdminIds = $this->getDataLimitAdminIds();
        if ($dataLimitAdminIds && !in_array($row[$this->dataLimitField], $dataLimitAdminIds)) {
            return $this->error(__('You have no permission'));
        }

        if ($this->request->isPost()) {
            $data = $this->request->post();
            if (!$data) {
                return $this->error(__('Parameter %s can not be empty', ['']));
            }

            // 模型表数据
            $modelTableData = [];
            foreach ($data as $key => $datum) {
                if (str_contains($key, $modelTableFullName . '__')) {
                    $fieldName                  = str_replace(['content.', $modelTableFullName . '__'], '', $key);
                    $modelTableData[$fieldName] = $this->model::setModelTableData($datum, $modelFieldsConfig[$fieldName] ?? '');
                }
            }

            $data = $this->excludeFields($data);

            if (empty($data['publish_time']) && $data['status'] == 'normal') {
                $data['publish_time'] = time();
            }

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

                if (isset($data['tags'])) {
                    // 自动新建标签
                    $data['tags'] = Helper::autoCreateTags($data['tags']);
                    // 增加的tag文档+1
                    foreach ($data['tags'] as $tag) {
                        if (!in_array($tag, $row->tags)) {
                            Tags::where('id', $tag)->inc('document_count')->save();
                        }
                    }
                }

                // 减少的tag文档-1
                foreach ($row->tags as $tag) {
                    if (isset($data['tags'])) {
                        if (!in_array($tag, $data['tags'])) {
                            Tags::where('id', $tag)->dec('document_count')->save();
                        }
                    } else {
                        Tags::where('id', $tag)->dec('document_count')->save();
                    }
                }

                // 用户动态
                if ($data['status'] == 'normal' && ($row->status == 'unaudited' || $row->status == 'refused') && !empty($data['user_id'])) {
                    $interaction = Server::getIni(Filesystem::fsFit(root_path() . 'modules/interaction/'));
                    if ($interaction && $interaction['state'] == 1) {
                        $recentHtml = '发表了内容 <a href="/cms/info/' . $row->id . '">' . $data['title'] . '</a>';
                        \app\admin\model\user\Recent::create([
                            'user_id' => $data['user_id'],
                            'content' => $recentHtml,
                        ]);
                    }
                }

                $result = $row->save($data);
                Db::name($modelInfo['table'])->where('id', $data['id'])->update($modelTableData);
                $this->model->commit();
            } catch (Throwable $e) {
                $this->model->rollback();
                return $this->error($e->getMessage());
            }
            if ($result !== false) {
                return $this->success(__('Update successful'));
            } else {
                return $this->error(__('No rows updated'));
            }

        }

        $row            = $row->toArray();
        $modelTableData = Db::name($modelInfo['table'])->find($id);
        if ($modelTableData) {
            foreach ($modelTableData as $key => $modelTableDatum) {
                if ($key != 'id') $row[$modelTableFullName . '__' . $key] = $this->model::getModelTableData($modelTableDatum, $modelFieldsConfig[$key] ?? '');
            }
        }
        return $this->success('', [
            'row' => $row
        ]);
    }

    /**
     * 删除
     * @param array $ids
     * @throws Throwable
     */
    public function del(array $ids = []): Response
    {
        if ($this->request->method() != 'DELETE' || !$ids) {
            return $this->error(__('Parameter error'));
        }

        $modelTable = $this->request->input('content_model_table', '');
        $modelInfo  = Db::name('cms_content_model')
            ->where('table', $modelTable)
            ->find();
        if (!$modelInfo) {
            return $this->error(__('The model cannot be found'));
        }

        $routePath = ($this->request->controller() ?? '') . '/' . $this->request->action;
        if (!Member::check($routePath)) {
            return $this->error(__('You have no permission'), [], 401);
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
                Db::name($modelInfo['table'])->where('id', $v->id)->delete();
                $count += $v->delete();
            }
            $this->model->commit();
        } catch (Throwable $e) {
            $this->model->rollback();
            return $this->error($e->getMessage());
        }
        if ($count) {
            return $this->success(__('Deleted successfully'));
        } else {
            return $this->error(__('No rows were deleted'));
        }
    }

    /**
     * 批量修改状态
     */
    public function status(): Response
    {
        // 验 cms/content 权限
        $routePath = ($this->app->request->controllerPath ?? '');
        if (!Member::check($routePath)) {
            return $this->error(__('You have no permission'), [], 401);
        }

        $ids    = $this->request->input('ids/a', []);
        $status = $this->request->input('status/s', '');
        foreach ($ids as $id) {
            Db::name('cms_content')
                ->where('id', $id)
                ->update([
                    'status' => $status,
                ]);
        }
        return $this->success();
    }
}