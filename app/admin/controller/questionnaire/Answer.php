<?php

namespace app\admin\controller\questionnaire;

use app\common\controller\Backend;
use think\facade\Db;
use Throwable;

/**
 * 答题管理
 */
class Answer extends Backend
{
    /**
     * Answer模型对象
     * @var object
     * @phpstan-var \app\admin\model\questionnaire\Answer
     */
    protected object $model;

    protected array|string $preExcludeFields = ['id'];

    protected string|array $quickSearchField = ['id'];

    protected array $type = [0, 1, 2, 3, 4];//题目类型

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\questionnaire\Answer;
    }


    /**
     * 若需重写查看、编辑、删除等方法，请复制 @see \app\admin\library\traits\Backend 中对应的方法至此进行重写
     */

    /**
     * 查看
     * @throws Throwable
     */
    public function index(): void
    {
        if ($this->request->param('select')) {
            $this->select();
        }

        $ids = $this->getIds();
        list($where, $alias, $limit, $order) = $this->queryBuilder();
        $where[] = [
            'answer.id',
            'in',
            $ids
        ];

        $res = $this->model
            ->field($this->indexField)
            ->alias($alias)
            ->where($where)
            ->order($order)
            ->paginate($limit);

        if ($res->total() > 0) {
            foreach ($res->items() as &$item) {
                $options   = implode(',', $item->options);
                $array     = $this->model
                    ->where('title', '=', $item->title)
                    ->where('type', '=', $item->type)
                    ->where('must', '=', $item->must)
                    ->where('options', '=', $options)
                    ->column('id');
                $item->num = count($array);
                $item->ids = implode(',', $array);
            }
            unset($item);
        }

        $this->success('', [
            'list'   => $res->items(),
            'total'  => $res->total(),
            'remark' => '',
        ]);
    }

    /**
     *获取去重后可展示的答题
     */
    public function getIds(): array
    {
        $cursor = $this->model
            ->where('type', 'in', $this->type)
            ->distinct(true)
            ->field('title,type,must,options')
            ->cursor();

        $ids = [];
        if ($cursor->valid()) {
            foreach ($cursor as $value) {
                $options = implode(',', $value['options']);

                $id = $this->model
                    ->where('title', '=', $value['title'])
                    ->where('type', '=', $value['type'])
                    ->where('must', '=', $value['must'])
                    ->where('options', '=', $options)
                    ->value('id');

                $ids[] = $id;
            }
        }
        return $ids;
    }

    /**
     * 分析-单选、多选题
     */
    public function analyse(): void
    {
        $params = $this->request->post();

        $where = [
            ['title', '=', $params['title']],
            ['type', '=', $params['type']],
            ['must', '=', $params['must']],
            ['options', '=', $params['options']]
        ];

        $answer = Db::name('questionnaire_answer')
            ->where($where)
            ->field('checked')
            ->select()
            ->toArray();

        $options = $this->handleArray($params['options']);

        if ($params['type'] == 1) {
            //多选题
            foreach ($options as $k => $v) {
                foreach ($answer as $value) {
                    $checked = explode(',', $value['checked']);
                    foreach ($checked as $kk => $vv) {
                        if ($v['name'] == $vv) {
                            $options[$k]['value']++;
                        }
                    }
                }
            }
        } else {
            //单选题
            foreach ($options as $k => $v) {
                foreach ($answer as $value) {
                    if ($v['name'] == $value['checked']) {
                        $options[$k]['value']++;
                    }
                }
            }
        }
        $compute = $this->compute($options);
        $zhu     = $compute['zhu'];
        $yuan    = $compute['yuan'];

        $this->success('', [
            'yuan' => $yuan,
            'zhu'  => $zhu,
        ]);
    }

    /**
     * 分析-填空题
     */
    public function gap(): void
    {
        $params = $this->request->post();

        $where = [
            ['title', '=', $params['title']],
            ['type', '=', $params['type']],
            ['must', '=', $params['must']],
            ['options', '=', $params['options']],
            ['checked', '<>', '']
        ];
        $page  = $params['page'];
        $list  = Db::name('questionnaire_answer')
            ->where($where)
            ->withAttr('create_time', function ($v, $d) {
                return date('Y-m-d H:i:s', $v);
            })
            ->field('checked,create_time')
            ->limit(20)
            ->page($page)
            ->select()
            ->toArray();

        $hasMore = true;
        if (count($list) < 20) {
            $hasMore = false;
        }
        $this->success('', [
            'list'    => $list,
            'hasMore' => $hasMore
        ]);
    }

    /**
     * 数组处理
     */
    public function handleArray(string $options): array
    {
        $array   = [];
        $options = explode(',', $options);
        foreach ($options as $k => $v) {
            $array[] = [
                'name'  => $v,
                'value' => 0
            ];
        }
        return $array;
    }

    /**
     * 计算
     */
    public function compute(array $options): array
    {
        $zhu = [
            array_column($options, 'name'),
            array_column($options, 'value')
        ];
        return [
            'zhu'  => $zhu,
            'yuan' => $options
        ];
    }
}