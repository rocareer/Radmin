<?php

namespace modules\cms\library;

use app\admin\model\cms\Content;
use app\admin\model\UserMoneyLog;
use app\common\model\User;
use think\facade\Db;
use app\admin\model\cms\Tags;
use think\facade\Log;
use Throwable;

class Helper
{

    public static function generateSn(): string
    {
        $orderIdMain = date('YmdHis') . rand(10000000, 99999999);
        $orderIdLen  = strlen($orderIdMain);
        $orderIdSum  = 0;
        for ($i = 0; $i < $orderIdLen; $i++) {
            $orderIdSum += (int)(substr($orderIdMain, $i, 1));
        }
        return $orderIdMain . str_pad((100 - $orderIdSum % 100) % 100, 2, '0', STR_PAD_LEFT);
    }

    /**
     * 分成
     */
    public static function divideInto($payLog): void
    {
        Db::startTrans();
        try {
            $cmsConfigArr  = [];
            $remarkEbak    = $payLog->remark;
            $cmsConfigData = Db::name('cms_config')
                ->where('name', 'in', ['appreciation_ratio', 'buy_ratio', 'wechat_payment_commission'])
                ->select();
            foreach ($cmsConfigData as $item) {
                $cmsConfigArr[$item['name']] = $item['value'];
            }

            $authorAmount     = 0;// 作者可得
            $commissionRatio  = bcdiv($cmsConfigArr['wechat_payment_commission'], 100, 4);// 手续费比例
            $commissionAmount = bcmul($payLog->amount, $commissionRatio, 4);// 手续费金额
            $shareableAmount  = bcsub($payLog->amount, $commissionAmount, 4);// 可分成金额
            if ($payLog->project == 'admire' && $cmsConfigArr['appreciation_ratio'] > 0) {
                // 赞赏分成
                $ratio        = bcdiv($cmsConfigArr['appreciation_ratio'], 100, 4);
                $authorAmount = bcmul($shareableAmount, $ratio, 2);
            } elseif ($payLog->project == 'content' && $cmsConfigArr['buy_ratio'] > 0) {
                // 内容付费分成
                $ratio        = bcdiv($cmsConfigArr['buy_ratio'], 100, 4);
                $authorAmount = bcmul($shareableAmount, $ratio, 2);
            }

            if ($commissionAmount > 0) {
                $payLog->remark = ($payLog->remark ? $payLog->remark . '；' : '') . '收款方式从付款总金额扣除了 ' . $commissionAmount . '元 手续费';
            }

            if ($authorAmount > 0 && $payLog->object_id) {
                // 取得文章作者，作者可能为管理员
                $contentUserId = Db::name('cms_content')
                    ->where('id', $payLog->object_id)
                    ->value('user_id');
                if ($contentUserId) {
                    // 确定会员存在，以免 UserMoneyLog 抛出异常
                    $user = User::where('id', $contentUserId)->value('id');
                    if ($user) {
                        UserMoneyLog::create([
                            'user_id' => $contentUserId,
                            'money'   => $authorAmount,
                            'memo'    => $payLog->title . ($remarkEbak && $payLog->project == 'admire' ? '；用户留言：' . $remarkEbak : ''),
                        ]);
                        $payLog->remark = ($payLog->remark ? $payLog->remark . '；' : '') . $authorAmount . '元 已经自动发放至作者会员账户余额，会员ID：' . $contentUserId;
                    }
                }
            }

            $payLog->save();
            Db::commit();
        } catch (Throwable $e) {
            Log::write('分成失败：' . $e->getMessage() . '【' . json_encode($payLog) . '】', 'error');
            Db::rollback();
        }
    }

    /*
     * 获取频道的子频道ID
     * @param int $id 频道ID
     */
    public static function getChannelChildrenIds($id): array
    {
        $ids      = [];
        $children = Db::name('cms_channel')->where('pid', $id)->select()->toArray();
        foreach ($children as $child) {
            $ids[] = $child['id'];
            $ids   = array_merge($ids, self::getChannelChildrenIds($child['id']));
        }
        return $ids;
    }

    /*
     * 根据子频道ID递归的获取所有父频道资料
     * @param int $id 频道ID
     */
    public static function getParentChannel($id, $parentsInfo = [])
    {
        $channel = Db::name('cms_channel')->where('id', $id)->find();
        if ($channel) {
            $parentsInfo[] = $channel;
            if ($channel['pid'] > 0) {
                $parentsInfo = self::getParentChannel($channel['pid'], $parentsInfo);
            }
        }
        return $parentsInfo;
    }

    /*
     * 获取区块数据
     */
    public static function getBlock(string $name): array
    {
        $blockData = Db::name('cms_block')
            ->where('name', $name)
            ->where('status', 1)
            ->where(function ($query) {
                $query->where('start_time', '<=', time())->whereOr('start_time', 'null');
            })
            ->where(function ($query) {
                $query->where('end_time', '>', time())->whereOr('end_time', 'null');
            })
            ->order('weigh', 'desc')
            ->select()->toArray();
        foreach ($blockData as &$blockDatum) {
            if ($blockDatum['type'] == 'image' || $blockDatum['type'] == 'carousel') {
                if (!$blockDatum['image']) continue;
                $blockDatum['image'] = full_url($blockDatum['image']);
            }
        }
        return $blockData;
    }

    /**
     * 字段字典数据
     */
    public static function restoreDict($comment)
    {
        $comment = explode(':', $comment);
        if (!isset($comment[1])) return '';
        $dist = str_replace(',', "\n", $comment[1]);
        return str_attr_to_array($dist);
    }

    /**
     * 字段默认值
     */
    public static function restoreDefault($default, $type)
    {

        if ($type == 'number') {
            return (float)$default;
        } elseif ($default == '0') {
            return '0';
        } elseif ($type == 'array') {
            return [];
        } elseif (in_array($type, ['checkbox', 'selects', 'city', 'images', 'files', 'remoteSelects'])) {
            if (empty($default)) {
                return [];
            }
            if (!is_array($default)) {
                return explode(',', $default);
            }
            return $default;
        } else {
            return $default;
        }
    }

    public static function autoCreateTags($tagsId)
    {
        $newTags  = [];
        $tagModel = new Tags();
        $dbTagIds = $tagModel::where('id', 'in', $tagsId)->column('id');
        foreach ($tagsId as $key => $id) {
            if (!in_array($id, $dbTagIds)) {
                unset($tagsId[$key]);
                $newTags[] = [
                    'name' => $id,
                ];
            }
        }
        $newTags = $tagModel->saveAll($newTags);
        foreach ($newTags as $newTag) {
            $tagsId[] = $newTag->id;
        }
        return $tagsId;
    }

    /**
     * 从数据库中获取表字段信息
     */
    public static function getTableFields(string $table): array
    {
        if (!$table) return [];
        $dbname = config('database.connections.mysql.database');
        $prefix = config('database.connections.mysql.prefix');

        $sql        = "SELECT * FROM `information_schema`.`columns` "
            . "WHERE TABLE_SCHEMA = ? AND table_name = ? "
            . "ORDER BY ORDINAL_POSITION";
        $columnList = Db::query($sql, [$dbname, $table]);
        if (!$columnList) {
            $columnList = Db::query($sql, [$dbname, $prefix . $table]);
        }

        $fieldList = [];
        foreach ($columnList as $item) {
            $fieldList[$item['COLUMN_NAME']] = $item;
        }
        return $fieldList;
    }

    /**
     * 获取 uni-app 首页文章数据
     * @throws Throwable
     */
    public static function getUnIndexContents($userIsLogin, $channel): \think\Paginator
    {
        $limit = request()->request('limit');
        return Content::where('status', 'normal')
            ->where('flag', 'find in set', 'recommend')
            ->where(function ($query) use ($userIsLogin, $channel) {
                if (!$userIsLogin) {
                    $query->where('allow_visit_groups', 'all');
                }

                if ($channel) {
                    $channelIds   = self::getChannelChildrenIds($channel);
                    $channelIds[] = $channel;

                    $query->where('channel_id', 'in', $channelIds);
                }
            })
            ->orderRaw("IF(flag LIKE '%top%', 1, 0) DESC")
            ->order('weigh', 'desc')
            ->paginate($limit);
    }
}