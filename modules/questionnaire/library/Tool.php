<?php

namespace modules\questionnaire\library;

use ba\Random;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode as QR;
use think\facade\Cache;
use Throwable;

class Tool
{
    /**
     * 生成二维码
     * @param string $params 二维码图片存储的数据
     * @param string $old    老的二维码图片链接
     * @return array
     */
    public static function qrCode(string $params, string $old = ''): array
    {
        $name       = Random::build() . '.png';
        $returnData = [
            'code' => 1,
            'msg'  => 'ok',
            'url'  => '/storage/questionnaire/qrcode/' . $name
        ];
        $rootPath   = app()->getRootPath();
        try {
            $qrCode = new QR($params);
            $qrCode->setEncoding('UTF-8');
            $qrCode->setSize(200);
            $qrCode->setMargin(5);
            $qrCode->setErrorCorrectionLevel(ErrorCorrectionLevel::HIGH());

            //二维码保存路径
            $dir = $rootPath . 'public/storage/questionnaire/qrcode/';
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            if ($old) {
                $file = $rootPath . 'public' . $old;
                if (file_exists($file)) {
                    unlink($file);
                }
            }
            $qrCode->writeFile($dir . $name);

        } catch (\Throwable $e) {
            $returnData = [
                'code' => 0,
                'msg'  => $e->getMessage()
            ];
        }

        return $returnData;
    }

    /**
     * 生成小程序码
     * @param string $params 小程序码存储的参数
     * @param string $old    老的小程序码图片地址
     * @return array
     */
    public static function miniCode(string $params, string $old = ''): array
    {
        $rootPath = app()->getRootPath();
        //小程序码保存路径
        $dir = $rootPath . 'public/storage/questionnaire/minicode/';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        if ($old) {
            $file = $rootPath . 'public' . $old;
            if (file_exists($file)) {
                unlink($file);
            }
        }

        return self::createMiniCode([
            'page'       => 'pages/index/index',
            'scene'      => $params,
            'width'      => 200,
            'check_path' => false
        ], $dir);
    }

    /**
     * 获取 access_token
     * @return string
     */
    protected static function getAccessToken(): string
    {
        $key          = 'questionnaire_access_token';
        $access_token = Cache::get($key);
        if ($access_token) {
            return $access_token;
        } else {
            $config   = Config::getConfig();
            $url      = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $config['questionnaire_mini']['appid'] . "&secret=" . $config['questionnaire_mini']['secret'];
            $resp     = file_get_contents($url);
            $respData = json_decode($resp, true);
            Cache::tag(Config::$cacheTag)->set($key, $respData['access_token'], 7000);
            return $respData['access_token'];
        }
    }

    /**
     * 生产小程序码
     * @param array  $data 小程序码参数
     * @param string $dir  保存路径
     * @return array
     */
    protected static function createMiniCode(array $data, string $dir): array
    {
        $json_data = json_encode($data);
        // 创建流上下文
        $options = [
            'http' => [
                'method'  => 'POST',
                'header'  => 'Content-Type: application/json' . "\r\n" .
                    'Content-Length: ' . strlen($json_data) . "\r\n",
                'content' => $json_data
            ]
        ];

        $context = stream_context_create($options);
        $name    = Random::build() . '.png';

        // 发送请求并获取响应
        try {
            $token = self::getAccessToken();
            $url   = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=" . $token;
            $resp  = file_get_contents($url, false, $context);
            file_put_contents($dir . $name, $resp);

        } catch (Throwable $e) {
            return [
                'code' => 0,
                'msg'  => $e->getMessage()
            ];
        }
        return [
            'code' => 1,
            'url'  => '/storage/questionnaire/minicode/' . $name
        ];
    }

    /**
     * 转换题目类型文本
     * @param int $type 类型
     * @return string
     */
    public static function transitionType(int $type): string
    {
        switch ($type) {
            case 1:
                $string = '多选题';
                break;
            case 2:
                $string = '填空题';
                break;
            case 3:
                $string = '简答题';
                break;
            case 4:
                $string = '下拉框';
                break;
            case 5:
                $string = '图片';
                break;
            case 6:
                $string = '视频';
                break;
            case 7:
                $string = '文件';
                break;
            default:
                $string = '单选题';
                break;
        }
        return $string;
    }

    /**
     * 文件类型
     * @param string $path 文件地址
     * @return string
     */
    public static function fileType(string $path): string
    {
        $parts = explode('.', $path);
        $ext   = end($parts);

        $types = [
            'pdf'   => ['pdf', 'ppt'],
            'word'  => ['doc', 'docx'],
            'excel' => ['xls', 'xlsx'],
            'zip'   => ['zip', 'rar', '7z'],
        ];

        foreach ($types as $k => $v) {
            if (in_array($ext, $v)) return $k;
        }
        return 'file';
    }

    /**
     * 删除文件
     * @param array $files 文件
     * @return bool
     */
    public static function delFile(array $files): bool
    {
        foreach ($files as $file) {
            if (file_exists($file) && is_file($file)) {
                unlink($file);
            }
        }
        return true;
    }
}