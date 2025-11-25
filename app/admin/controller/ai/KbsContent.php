<?php

namespace app\admin\controller\ai;

use Throwable;
use ba\Filesystem;
use think\facade\Log;
use voku\helper\HtmlDomParser;
use app\common\library\ai\Redis;
use app\common\library\ai\Helper;
use app\common\controller\Backend;
use app\common\model\ai\ChatModel;
use app\common\library\ai\Embeddings;
use voku\helper\SimpleHtmlDomInterface;
use app\common\library\ai\AiInterrupter;

/**
 * 知识点管理
 */
class KbsContent extends Backend
{
    /**
     * KbsContent模型对象
     * @var object
     * @phpstan-var \app\common\model\ai\KbsContent
     */
    protected object $model;

    protected string|array $defaultSortField = 'weigh,desc';

    protected array|string $preExcludeFields = ['id', 'embedding', 'update_time', 'create_time'];

    protected string|array $quickSearchField = ['title', 'content'];

    protected array $noNeedPermission = ['calcTokens', 'embeddings', 'search'];

    protected array $config = [];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\common\model\ai\KbsContent;
        $this->request->filter('trim');
        $this->config                         = Helper::getConfig();
        $this->config['text_type_switch']     = Helper::$embeddingModelAttr[$this->config['ai_api_type']]['text_type'] ?? false;
        $this->config['max_embedding_tokens'] = Helper::$embeddingModelAttr[$this->config['ai_api_type']]['max_tokens'] ?? 0;
    }

    /**
     * 设置redis缓存
     * @throws Throwable
     */
    public function setRedisJson($kbs)
    {
        // 直接重查
        $data = $this->model
            ->field('id,ai_kbs_ids,type,title,model,text_type,content,embedding,content_source,content_quote')
            ->where('id', $kbs['id'])
            ->find();
        if (in_array($kbs['status'], ['success', 'usable']) && $data->embedding && $this->config['ai_work_mode'] == 'redis') {
            $cacheData = array_merge($data->toArray(), [
                'ai_kbs_ids' => $data->getOrigin('ai_kbs_ids'),
            ]);

            $msg = '';
            try {
                $redis = Redis::instance();
                if (!$redis->jsonSet($data->id, $cacheData)) {
                    $msg = '设置 redis 缓存失败！';
                }
                $redis->save();
            } catch (Throwable $e) {
                $msg = $e->getMessage();
            }
            if (!$msg) {
                $data->status = 'usable';
            } else {
                $data->status = 'success';
                $data->extend = array_merge($kbs['extend'], [
                    'error' => $msg
                ]);
            }
            $data->save();
        }
    }

    /**
     * 批量向量转换
     * @throws Throwable
     */
    public function embeddings()
    {
        $ids               = $this->request->post('ids/a', []);
        $where             = [];
        $dataLimitAdminIds = $this->getDataLimitAdminIds();
        if ($dataLimitAdminIds) {
            $where[] = [$this->dataLimitField, 'in', $dataLimitAdminIds];
        }

        if (count($ids) > 10) {
            $this->error('请选择10条以内的数据向量化~');
        }

        $pk                 = $this->model->getPk();
        $where[]            = [$pk, 'in', $ids];
        $data               = $this->model->where($where)->select();
        $awaitEmbeddingData = $texts = [
            'query'    => [],
            'document' => [],
        ];
        foreach ($data as $item) {
            $text = Helper::getNeedEmbeddingContent($item->toArray());
            if ($item['text_type'] == 'query') {
                $texts['query'][]              = $text;
                $awaitEmbeddingData['query'][] = $item;
            } else {
                $texts['document'][]              = $text;
                $awaitEmbeddingData['document'][] = $item;
            }
        }

        try {
            $instance = Embeddings::instance();
            foreach ($texts as $sKey => $sText) {
                if (empty($sText)) continue;
                $embeddingData = $instance->texts($sText, $sKey);

                // 按向量数据的顺序进行赋值
                foreach ($awaitEmbeddingData[$sKey] as $key => $awaitEmbeddingDatum) {
                    $itemData = [];
                    $this->embeddingComplete($itemData, $embeddingData, $key, [
                        'batch'      => true,
                        'batch_data' => $ids,
                    ]);
                    $awaitEmbeddingDatum->save($itemData);
                    $this->setRedisJson($awaitEmbeddingDatum);
                }
            }
        } catch (Throwable $e) {
            $this->error($e->getMessage());
        }
        $this->success();
    }

    /**
     * 向量化请求完成后的数据记录
     */
    public function embeddingComplete(&$data, $embeddingData, $key = 0, $extend = [])
    {
        if ($embeddingData['code'] == 0) {
            $data['tokens'] = 0;
            $data['status'] = 'fail';
            $data['extend'] = array_merge(['error' => $embeddingData['msg']], $extend);
        } else {
            $data['model']     = $embeddingData['model'];
            $data['tokens']    = $embeddingData['tokens'];
            $data['embedding'] = $embeddingData['data'][$key]['embedding'];
            $data['status']    = $this->config['ai_work_mode'] == 'mysql' ? 'usable' : 'success';
            $data['extend']    = $extend;
        }
    }

    /**
     * 创建/重建索引
     * @throws Throwable
     */
    public function indexSet()
    {
        $instance = Redis::instance();

        $newSet = true;
        if ($instance->indexIsSet()) {
            $instance->indexDel();
            $newSet = false;
        }
        $instance->indexSet();
        $this->success(($newSet ? '创建' : '重建') . '索引成功~');
    }

    /**
     * 缓存所有向量
     * @throws Throwable
     */
    public function checkCache()
    {
        $ids     = $this->request->post('ids/a', []);
        $content = $this->model
            ->field('id,ai_kbs_ids,type,title,model,text_type,content,embedding,content_source,content_quote')
            ->where('status', 'in', 'success,usable')
            ->where('embedding', 'not null')
            ->where(function ($query) use ($ids) {
                $ids = array_filter($ids);
                if ($ids) {
                    $query->where('id', 'in', $ids);
                }
            })
            ->select();
        $count   = 0;
        try {
            $instance = Redis::instance();
            foreach ($content as $item) {
                $cacheData = array_merge($item->toArray(), [
                    'ai_kbs_ids' => $item->getOrigin('ai_kbs_ids'),
                ]);

                if ($instance->jsonSet($item->id, $cacheData)) {
                    $count++;
                    if ($item->status !== 'usable') {
                        $item->status = 'usable';
                        $item->save();
                    }
                }
            }
            $instance->save();
        } catch (Throwable $e) {
            $this->error($e->getMessage());
        }

        $this->success('成功缓存了 ' . $count . ' 条知识点~');
    }

    public function batchAdd()
    {
        $aiKbsIds = $this->request->post('ai_kbs_ids/a', []);
        $qas      = $this->request->post('qas/a', []);
        $url      = $this->request->post('url', '');
        if (empty($qas)) {
            $this->error('请输入问答对！');
        }
        $qasData = [];
        foreach ($qas as $qa) {
            if (isset($qa['q']) && isset($qa['a']) && $qa['q'] && $qa['a']) {
                $qasData[] = [
                    'ai_kbs_ids' => $aiKbsIds,
                    'type'       => 'qa',
                    'title'      => $qa['q'],
                    'content'    => $qa['a'],
                    'source'     => $url,
                ];
            }
        }
        $contentModel = new \app\common\model\ai\KbsContent();
        $contentModel->saveAll($qasData);
        $this->success();
    }

    public function generateQa()
    {
        $model       = $this->request->post('model');
        $content     = $this->request->post('content');
        $modelConfig = ChatModel::where('name', $model)
            ->where('status', 1)
            ->order('weigh desc')
            ->find();

        $clientOptions = [];
        if ($modelConfig['proxy']) {
            $clientOptions['proxy'] = $modelConfig['proxy'];
        }

        $client = Helper::getClient($clientOptions, [
            'Authorization' => 'Bearer ' . $modelConfig['api_key'],
            'Content-Type'  => 'application/json',
        ], false);

        try {
            $url = $modelConfig['api_url'] ?: Helper::$chatModelAttr[$modelConfig['name']]['api_url'];

            $postJson = [
                'model' => $modelConfig['name'],
            ];

            $messages = [
                [
                    'role'    => 'system',
                    'content' => '你是一个数据工程师。',
                ],
                [
                    'role'    => 'user',
                    'content' => "我会给你一段文本，其中可能包含多个主题内容，学习它们，并整理学习成果，要求为:\n1. 提出最多25个问题。\n2. 给出每个问题的答案。\n3. 答案要详细完整，答案可以包含普通文字、链接、代码、表格、公式、媒体链接等markdown元素。\n4. 按格式返回多个问题和答案:\nQ1:问题。\nA1:答案。\n\nQ2:\nA2:\n.....\n我的文本:$content",
                ]
            ];

            if (Helper::$chatModelAttr[$modelConfig['name']]['api_type'] == 'ali') {
                $postJson['input']['messages'] = $messages;
                $postJson['parameters']        = [
                    'temperature'   => 1.0,
                    'result_format' => 'message',
                ];
            } else {
                $postJson['messages'] = $messages;
                $postJson             = [
                    'temperature' => 1.0,
                ];
            }

            $response = $client->post($url, [
                'json'         => $postJson,
                'read_timeout' => 60,
            ]);
            $body     = $response->getBody();
            $content  = $body->getContents();
            $content  = json_decode($content, true);
            $output   = $content['output']['choices'][0]['message']['content'];

            Log::write($output, 'output');

            $outputFormat = [];
            $outputs      = explode("\n\n", $output);
            $sn           = 1;
            foreach ($outputs as $output) {
                $qas = explode("\n", $output);

                if (isset($qas[0]) && $qas[0]) {
                    $qas[0] = str_replace("Q$sn: ", '', $qas[0]);
                }
                if (isset($qas[1]) && $qas[1]) {
                    $qas[1] = str_replace("A$sn: ", '', $qas[1]);
                }

                $outputFormat[] = [
                    'q' => $qas[0] ?? '',
                    'a' => $qas[1] ?? '',
                ];
                $sn++;
            }
        } catch (AiInterrupter $e) {
            throw new AiInterrupter($e->getMessage());
        } catch (Throwable $e) {
            $this->error($e->getMessage() . ' - ' . $e->getFile() . ' - ' . $e->getLine());
        }

        $this->success('', [
            'output' => $outputFormat
        ]);
    }

    public function getFileContent()
    {
        $file     = $this->request->get('file');
        $filePath = Filesystem::fsFit(public_path() . $file);
        if (!is_file($filePath)) {
            $this->error('文件未找到~');
        }
        $this->success('', [
            'content' => file_get_contents($filePath)
        ]);
    }

    public function getUrlContent()
    {
        $url = $this->request->get('url');
        if (!$url) {
            $this->error('请输入URL');
        }

        $html = HtmlDomParser::file_get_html($url);
        if ($html) {
            $content = '';

            /**
             * 一个预处理buildadmin文档网页的示例-start
             */
            // 查找文档内容主体的标签
            $element = $html->findOneOrFalse('main.page .content__default');
            if ($element) {
                // 遍历标签
                foreach ($element->childNodes() as $childNode) {
                    if (empty(trim($childNode->textContent))) continue;

                    if (in_array($childNode->nodeName, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'])) {
                        // 标题
                        $content .= $this->contentHandle($childNode) . "\n";
                    } elseif ($childNode->nodeName == 'div' && $childNode->classList->contains('extra-class')) {
                        // 代码块
                        $codeLines = $childNode->findOneOrFalse('pre code');
                        if ($codeLines) {
                            $content .= "```\n$childNode->textContent```\n";
                        }
                    } elseif ($childNode->nodeName == 'table') {
                        // 表格
                        $tableContent = '';

                        $thead = $childNode->findMultiOrFalse('thead tr th');
                        if (!$thead) continue;

                        $tableContent .= "|";
                        foreach ($thead as $item) {
                            $tableContent .= ' ' . $this->contentHandle($item) . ' |';
                        }

                        $tbody = $childNode->findMultiOrFalse('tbody tr');
                        if (!$tbody) continue;

                        $tableContent .= "\n|";
                        foreach ($tbody as $item) {
                            $tds = $item->findMultiOrFalse('td');
                            foreach ($tds as $td) {
                                $tableContent .= ' ' . $this->contentHandle($td) . ' |';
                            }
                            $tableContent .= "\n";
                        }
                        $tableContent .= "\n";

                        $content .= "\n$tableContent\n";
                    } elseif (in_array($childNode->nodeName, ['ul', 'ol'])) {
                        // 列表
                        $liCount = 1;
                        foreach ($childNode->childNodes() as $liNode) {
                            if (empty(trim($liNode->textContent))) continue;

                            if ($childNode->childElementCount > 1) {
                                // 添加序号
                                $content .= $liCount . '. ' . $this->contentHandle($liNode) . "\n";
                                $liCount++;
                            } else {
                                $content .= $this->contentHandle($liNode) . "\n";
                            }
                        }
                    } else {
                        // 其他元素
                        $content .= $this->contentHandle($childNode) . "\n";
                    }
                }
            }
            /**
             * 一个预处理buildadmin文档网页的示例-end
             */

            if (!$content) {
                $content = $html->plaintext;
            }
        }
        $this->success('', [
            'content' => $content ?? '获取失败或无内容！'
        ]);
    }

    /**
     * 提取文本
     * @param $node SimpleHtmlDomInterface
     * @return string
     */
    public function contentHandle(SimpleHtmlDomInterface $node): string
    {
        $content = '';
        foreach ($node->childNodes() as $childNode) {
            if (empty(trim($childNode->textContent))) continue;

            // 加粗、行内代码、链接
            if ($childNode->nodeName == 'code') {
                $content .= "`$childNode->textContent`";
            } elseif ($childNode->nodeName == 'strong') {
                $content .= "**$childNode->textContent**";
            } elseif ($childNode->nodeName == 'a' && $childNode->getAttribute('target') == '_blank') {
                $content .= '[' . $childNode->textContent . '](' . $childNode->getAttribute('href') . ')';
            } elseif ($childNode->class == 'custom-block-title' && in_array($childNode->textContent, ['TIP', 'WARNING'])) {
                continue;
            } else {
                $content .= $childNode->textContent;
            }
        }
        return str_replace(['  (opens new window)', '(opens new window)'], '', $content);
    }


    /**
     * 搜索知识库
     * @throws Throwable
     */
    public function search()
    {
        if (!$this->auth->check('ai/search')) {
            $this->error('您没有权限操作！');
        }
        if ($this->request->isPost()) {
            $keywords = $this->request->post('keywords');
            $textType = $this->request->post('text_type', 'query');
            $kbs      = $this->request->post('kbs/a', []);
            $type     = $this->request->post('type', '');
            if (!$keywords) {
                $this->error('请输入关键字');
            }

            $where = [];
            if ($kbs) $where['kbs'] = array_filter($kbs);
            if ($type) $where['type'] = $type;
            if (Helper::$embeddingModelAttr[$this->config['ai_api_type']]['text_type']) {
                $where['text_type'] = $textType;
            }

            try {
                $instance = Embeddings::instance();
                $match    = $instance->getTextMatchData($keywords, 20, $textType, $where);
            } catch (Throwable $e) {
                $msg = $e->getMessage();
                if ($msg == config('ai.redis.index_name') . ': no such index') {
                    $msg = '请于知识点管理页面手动建立 redis 索引！';
                }
                $this->error($msg);
            }

            $this->success('', [
                'matchData' => $match['data'] ?? [],
            ]);
        }

        $this->success('', [
            'text_type_switch' => $this->config['text_type_switch'],
        ]);
    }

    /**
     * 查看
     * @throws Throwable
     */
    public function index(): void
    {
        if ($this->request->param('select')) {
            $this->select();
        }

        list($where, $alias, $limit, $order) = $this->queryBuilder();
        $res = $this->model
            ->field($this->indexField)
            ->withJoin($this->withJoinTable, $this->withJoinType)
            ->alias($alias)
            ->where($where)
            ->order($order)
            ->paginate($limit)->each(function ($item) {
                unset($item->embedding);
            });

        $this->success('', [
            'list'                 => $res->items(),
            'total'                => $res->total(),
            'remark'               => get_route_remark(),
            'text_type_switch'     => $this->config['text_type_switch'],
            'redis'                => $this->config['ai_work_mode'] == 'redis',
            'max_embedding_tokens' => $this->config['max_embedding_tokens'],
        ]);
    }

    /**
     * 添加
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
                $data[$this->dataLimitField] = $this->auth->id;
            }

            if ($data['status'] == 'auto') {
                $texts = Helper::getNeedEmbeddingContent($data);

                $instance      = Embeddings::instance();
                $embeddingData = $instance->texts($texts, $data['text_type']);
                $this->embeddingComplete($data, $embeddingData);
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

                $this->setRedisJson($this->model);

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
        $id  = $this->request->param($this->model->getPk());
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

            $data = $this->excludeFields($data);

            if ($data['status'] == 'auto') {
                $texts = Helper::getNeedEmbeddingContent($data);

                $instance      = Embeddings::instance();
                $embeddingData = $instance->texts($texts, $data['text_type']);
                $this->embeddingComplete($data, $embeddingData);
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
                $result = $row->save($data);

                $this->setRedisJson($row);

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

        $content        = $row->getData('content');
        $row            = $row->toArray();
        $row['content'] = $content;

        $this->success('', [
            'row' => $row,
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

        $where             = [];
        $dataLimitAdminIds = $this->getDataLimitAdminIds();
        if ($dataLimitAdminIds) {
            $where[] = [$this->dataLimitField, 'in', $dataLimitAdminIds];
        }

        $pk      = $this->model->getPk();
        $where[] = [$pk, 'in', $ids];

        $count = 0;
        $data  = $this->model->where($where)->select();
        $this->model->startTrans();
        try {
            foreach ($data as $v) {
                $count += $v->delete();

                // 删除 redis 缓存
                if ($this->config['ai_work_mode'] == 'redis') {
                    $redis = Redis::instance();
                    $redis->jsonDel($v->id);
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