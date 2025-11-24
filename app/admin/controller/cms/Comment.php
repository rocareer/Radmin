<?php

namespace app\admin\controller\cms;

use extend\ra\FileUtil;
use extend\ra\SystemUtil;
use support\Response;
use Throwable;
use ParseDownExt;
use think\facade\Db;
use app\common\controller\Backend;
use app\admin\library\module\Server;

/**
 * 评论管理
 *
 */
class Comment extends Backend
{
    /**
     * Comment模型对象
     * @var object
     * @phpstan-var \app\admin\model\cms\Comment
     */
    protected object $model;

    protected string|array $defaultSortField = 'weigh,desc';

    protected string|array $preExcludeFields = ['id', 'create_time'];

    protected array $withJoinTable = ['user', 'cmsContent'];

    protected string|array $quickSearchField = ['id'];

    public function initialize(): void
    {
        parent::initialize();
        $this->model = new \app\admin\model\cms\Comment;
        $this->request->filter('clean_xss');
    }

    /**
     * 查看
     * @throws Throwable
     */
    public function index(): Response
    {
        // 如果是select则转发到select方法,若select未重写,其实还是继续执行index
        if ($this->request->input('select')) {
            $this->select();
        }

        list($where, $alias, $limit, $order) = $this->queryBuilder();
        $res = $this->model
            ->withJoin($this->withJoinTable, $this->withJoinType)
            ->alias($alias)
            ->where($where)
            ->order($order)
            ->visible(['user' => ['username'], 'cmsContent' => ['title']])
            ->paginate($limit);

        return $this->success('', [
            'list'   => $res->items(),
            'total'  => $res->total(),
            'remark' => SystemUtil::get_route_remark(),
        ]);
    }

    /**
     * 编辑
     * @throws Throwable
     */
    public function edit(): Response
    {
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

                $interaction = Server::getIni(FileUtil::fsFit(root_path() . 'modules/interaction/'));

                // 评论审核通过，向at用户发送私信
                $commentsReview = Db::name('cms_config')
                    ->where('name', 'comments_review')
                    ->value('value');

                if ($interaction && $interaction['state'] == 1 && $data['status'] == 'normal' && $row->status == 'unaudited' && $commentsReview == 'yes') {
                    $commentLanguage = Db::name('cms_config')
                        ->where('name', 'comment_language')
                        ->value('value');
                    $content         = !$data['content'] ? '' : htmlspecialchars_decode($data['content']);
                    if ($commentLanguage == 'markdown') {
                        // 解析 markdown，已关闭 ParseDown 的安全模式，但使用 clean_xss 过滤 xss
                        $parseDown = new ParseDownExt();
                        $content   = clean_xss($parseDown->text($data['content']));
                    }
                    $contentTitle = Db::name('cms_content')
                        ->where('id', $data['content_id'])
                        ->value('title');

                    // @了用户，发送私信
                    if ($data['at_user']) {
                        foreach ($data['at_user'] as $userId) {
                            if ($userId == $data['user_id']) continue;
                            $messageHtml = '我在 <a href="/cms/info/' . $data['content_id'] . '">' . $contentTitle . '</a> 的评论中@了你<br />';
                            $messageHtml .= '<div style="margin-top: 10px;padding: 10px;border-left:4px solid #dedfe0;">' . $content . '</div>';
                            Db::name('user_message')
                                ->insert([
                                    'user_id'      => $data['user_id'],
                                    'recipient_id' => $userId,
                                    'content'      => $messageHtml,
                                    'create_time'  => time()
                                ]);
                        }
                    }

                    // 用户动态
                    $recentHtml = '在 <a href="/cms/info/' . $data['content_id'] . '">' . $contentTitle . '</a> 发表了评论<br />';
                    $recentHtml .= '<div style="margin-top: 10px;padding: 10px;border-left:4px solid #dedfe0;">' . $content . '</div>';
                    \app\admin\model\user\Recent::create([
                        'user_id' => $data['user_id'],
                        'content' => $recentHtml,
                    ]);
                }

                $result = $row->save($data);
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

        return $this->success('', [
            'row' => $row
        ]);
    }

    /**
     * 批量修改状态
     */
    public function status(): Response
    {
        $ids    = $this->request->input('ids/a', []);
        $status = $this->request->input('status/s', '');
        foreach ($ids as $id) {
            Db::name('cms_comment')
                ->where('id', $id)
                ->update([
                    'status' => $status,
                ]);
        }
        return $this->success();
    }
}