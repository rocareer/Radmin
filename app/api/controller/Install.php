<?php
/**
 * File:        InstallOptimized.php
 * Author:      albert <albert@rocareer.com>
 * Created:     2025/11/23 10:00
 * Description: 优化后的安装控制器
 *
 * Copyright [2014-2026] [https://rocareer.com]
 * Licensed under the Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */

namespace app\api\controller;

use app\admin\model\Config;
use app\admin\model\User as UserModel;
use app\exception\BusinessException;
use Exception;
use extend\ba\Random;
use extend\ba\Terminal;
use extend\ba\Version;
use extend\ra\FileUtil;
use PDOException;
use support\member\role\admin\AdminModel;
use support\orm\Db;
use support\Response;
use think\db\exception\DbException;
use Throwable;

/**
 * 优化后的安装控制器
 */
class Install
{
    // 安装状态常量
    const STATUS_OK = 'ok';
    const STATUS_FAIL = 'fail';
    const STATUS_WARN = 'warn';

    // 安装锁文件
    const LOCK_FILE = 'install.lock';
    const INSTALL_COMPLETE_MARK = 'install-end';

    // 配置文件
    const DB_CONFIG_FILE = 'think-orm.php';
    const BUILD_CONFIG_FILE = 'buildadmin.php';
    const DIST_DIR = 'web' . DIRECTORY_SEPARATOR . 'dist';

    // 依赖版本要求
    const DEPENDENT_VERSIONS = [
        'php' => '8.0.2',
        'npm' => '9.8.1',
        'cnpm' => '7.1.0',
        'node' => '20.14.0',
        'yarn' => '1.2.0',
        'pnpm' => '6.32.13',
    ];

    protected $request;

    public function __construct()
    {
        $this->request = request();
    }

    /**
     * 命令执行窗口
     */
    public function terminal()
    {
        if ($this->isInstallComplete()) {
            return $this->error(__('Installation already completed'));
        }
        (new Terminal())->exec(false);
    }

    /**
     * 切换包管理器
     */
    public function changePackageManager()
    {
        if ($this->isInstallComplete()) {
            return $this->error(__('Installation already completed'));
        }

        $newPackageManager = request()->post('manager', config('terminal.npm_package_manager'));
        if (Terminal::changeTerminalConfig()) {
            return $this->success('', ['manager' => $newPackageManager]);
        }

        return $this->error(__('Failed to switch package manager. Please modify configuration file manually:%s', ['config/buildadmin.php']));
    }

    /**
     * 环境基础检查 - 优化版
     */
    public function envBaseCheck()
    {
        if ($this->isInstallComplete()) {
            return $this->error(__('The system has completed installation. If you need to reinstall, please delete the %s file first', ['public/' . self::LOCK_FILE]));
        }

        if (env('MYSQL_PASSWORD')) {
            return $this->error(__('检测到带有数据库配置的 .env 文件。请清理后再试一次!'));
        }

        $checks = $this->performEnvironmentChecks();

        return $this->success('', $checks);
    }

    /**
     * 执行环境检查 - 抽取为独立方法
     */
    private function performEnvironmentChecks(): array
    {
        $checks = [];

        // PHP版本检查
        $phpVersion = phpversion();
        $checks['php_version'] = [
            'describe' => $phpVersion,
            'state' => Version::compare(self::DEPENDENT_VERSIONS['php'], $phpVersion) ? self::STATUS_OK : self::STATUS_FAIL,
            'link' => $this->getPhpVersionLinks($phpVersion),
        ];

        // 配置文件权限检查
        $configWritable = $this->checkConfigWritable();
        $checks['config_is_writable'] = [
            'describe' => self::writableStateDescribe($configWritable),
            'state' => $configWritable ? self::STATUS_OK : self::STATUS_FAIL,
            'link' => $this->getConfigWritableLinks($configWritable),
        ];

        // Public目录权限检查
        $publicWritable = FileUtil::pathIsWritable(public_path());
        $checks['public_is_writable'] = [
            'describe' => self::writableStateDescribe($publicWritable),
            'state' => $publicWritable ? self::STATUS_OK : self::STATUS_FAIL,
            'link' => $this->getPublicWritableLinks($publicWritable),
        ];

        // PDO扩展检查
        $pdoAvailable = $this->checkPdoExtensions();
        $checks['php_pdo'] = [
            'describe' => $pdoAvailable ? __('already installed') : __('Not installed'),
            'state' => $pdoAvailable ? self::STATUS_OK : self::STATUS_FAIL,
            'link' => $this->getPdoLinks($pdoAvailable),
        ];

        // GD扩展检查
        $gdAvailable = $this->checkGdExtension();
        $checks['php_gd2'] = [
            'describe' => $gdAvailable ? __('already installed') : __('Not installed'),
            'state' => $gdAvailable ? self::STATUS_OK : self::STATUS_FAIL,
            'link' => $this->getGdLinks($gdAvailable),
        ];

        // proc_open函数检查
        $procAvailable = $this->checkProcFunctions();
        $checks['php_proc'] = [
            'describe' => $procAvailable ? __('Allow execution') : __('disabled'),
            'state' => $procAvailable ? self::STATUS_OK : self::STATUS_WARN,
            'link' => $this->getProcLinks($procAvailable),
        ];

        return $checks;
    }

    /**
     * 检查配置文件权限
     */
    private function checkConfigWritable(): bool
    {
        $configDir = base_path() . DIRECTORY_SEPARATOR . 'config';
        $dbConfigFile = $configDir . DIRECTORY_SEPARATOR . self::DB_CONFIG_FILE;
        return FileUtil::pathIsWritable($configDir) && FileUtil::pathIsWritable($dbConfigFile);
    }

    /**
     * 检查PDO扩展
     */
    private function checkPdoExtensions(): bool
    {
        return extension_loaded("PDO") && extension_loaded('pdo_mysql');
    }

    /**
     * 检查GD扩展
     */
    private function checkGdExtension(): bool
    {
        return extension_loaded('gd') && function_exists('imagettftext');
    }

    /**
     * 检查proc_open函数
     */
    private function checkProcFunctions(): bool
    {
        return function_exists('proc_open') && function_exists('proc_close') && function_exists('proc_get_status');
    }

    /**
     * 获取PHP版本相关链接
     */
    private function getPhpVersionLinks(string $phpVersion): array
    {
        $phpVersionCompare = Version::compare(self::DEPENDENT_VERSIONS['php'], $phpVersion);
        if ($phpVersionCompare) {
            return [];
        }

        return [
            ['name' => __('need') . ' >= ' . self::DEPENDENT_VERSIONS['php'], 'type' => 'text'],
            [
                'name' => __('How to solve?'),
                'title' => __('Click to see how to solve it'),
                'type' => 'faq',
                'url' => 'https://doc.buildadmin.com/guide/install/preparePHP.html'
            ]
        ];
    }

    /**
     * 获取配置文件权限相关链接
     */
    private function getConfigWritableLinks(bool $writable): array
    {
        if ($writable) {
            return [];
        }

        return [
            [
                'name' => __('View reason'),
                'title' => __('Click to view reason'),
                'type' => 'faq',
                'url' => 'https://doc.buildadmin.com/guide/install/dirNoPermission.html'
            ]
        ];
    }

    /**
     * 获取Public目录权限相关链接
     */
    private function getPublicWritableLinks(bool $writable): array
    {
        if ($writable) {
            return [];
        }

        return [
            [
                'name' => __('View reason'),
                'title' => __('Click to view reason'),
                'type' => 'faq',
                'url' => 'https://doc.buildadmin.com/guide/install/dirNoPermission.html'
            ]
        ];
    }

    /**
     * 获取PDO相关链接
     */
    private function getPdoLinks(bool $available): array
    {
        if ($available) {
            return [];
        }

        return [
            ['name' => __('PDO extensions need to be installed'), 'type' => 'text'],
            [
                'name' => __('How to solve?'),
                'title' => __('Click to see how to solve it'),
                'type' => 'faq',
                'url' => 'https://doc.buildadmin.com/guide/install/missingExtension.html'
            ]
        ];
    }

    /**
     * 获取GD相关链接
     */
    private function getGdLinks(bool $available): array
    {
        if ($available) {
            return [];
        }

        return [
            ['name' => __('The gd extension and freeType library need to be installed'), 'type' => 'text'],
            [
                'name' => __('How to solve?'),
                'title' => __('Click to see how to solve it'),
                'type' => 'faq',
                'url' => 'https://doc.buildadmin.com/guide/install/gdFail.html'
            ]
        ];
    }

    /**
     * 获取proc相关链接
     */
    private function getProcLinks(bool $available): array
    {
        if ($available) {
            return [];
        }

        return [
            [
                'name' => __('View reason'),
                'title' => __('proc_open or proc_close functions in PHP Ini is disabled'),
                'type' => 'faq',
                'url' => 'https://doc.buildadmin.com/guide/install/disablement.html'
            ],
            [
                'name' => __('How to modify'),
                'title' => __('Click to view how to modify'),
                'type' => 'faq',
                'url' => 'https://doc.buildadmin.com/guide/install/disablement.html'
            ],
            [
                'name' => __('Security assurance?'),
                'title' => __('Using the installation service correctly will not cause any potential security problems. Click to view the details'),
                'type' => 'faq',
                'url' => 'https://doc.buildadmin.com/guide/install/senior.html'
            ]
        ];
    }

    /**
     * NPM环境检查 - 优化版
     */
    public function envNpmCheck()
    {
        if ($this->isInstallComplete()) {
            return $this->error('', [], 2);
        }

        $packageManager = request()->post('manager', 'none');
        $checks = $this->performNpmChecks($packageManager);

        return $this->success('', $checks);
    }

    /**
     * 执行NPM相关检查
     */
    private function performNpmChecks(string $packageManager): array
    {
        $checks = [];

        // NPM版本检查
        $npmVersion = Version::getVersion('npm');
        $npmVersionCompare = Version::compare(self::DEPENDENT_VERSIONS['npm'], $npmVersion);
        $checks['npm_version'] = [
            'describe' => $npmVersion ?: __('Acquisition failed'),
            'state' => $npmVersionCompare ? self::STATUS_OK : self::STATUS_WARN,
            'link' => $this->getNpmVersionLinks($npmVersion, $npmVersionCompare),
        ];

        // 包管理器版本检查
        if ($packageManager !== 'none') {
            $pmVersion = Version::getVersion($packageManager);
            $pmVersionCompare = Version::compare(self::DEPENDENT_VERSIONS[$packageManager], $pmVersion);
            $checks['pm_version'] = [
                'describe' => $pmVersion ?: __('Acquisition failed'),
                'state' => $pmVersionCompare ? self::STATUS_OK : self::STATUS_WARN,
                'link' => $this->getPmVersionLinks($packageManager, $pmVersion, $pmVersionCompare),
            ];
        }

        return $checks;
    }

    /**
     * 获取NPM版本相关链接
     */
    private function getNpmVersionLinks(?string $npmVersion, bool $compare): array
    {
        if ($compare && $npmVersion) {
            return [];
        }

        return [
            ['name' => __('need') . ' >= ' . self::DEPENDENT_VERSIONS['npm'], 'type' => 'text'],
            [
                'name' => __('How to solve?'),
                'title' => __('Click to see how to solve it'),
                'type' => 'faq',
                'url' => 'https://doc.buildadmin.com/guide/install/npmNode.html'
            ]
        ];
    }

    /**
     * 获取包管理器版本相关链接
     */
    private function getPmVersionLinks(string $packageManager, ?string $version, bool $compare): array
    {
        if ($compare && $version) {
            return [];
        }

        return [
            ['name' => __('need') . ' >= ' . self::DEPENDENT_VERSIONS[$packageManager], 'type' => 'text'],
            [
                'name' => __('How to solve?'),
                'title' => __('Click to see how to solve it'),
                'type' => 'faq',
                'url' => 'https://doc.buildadmin.com/guide/install/npmNode.html'
            ]
        ];
    }

    /**
     * 测试数据库连接 - 优化版
     */
    public function testDatabase()
    {
        $database = [
            'hostname' => $this->request->input('hostname'),
            'username' => $this->request->input('username'),
            'password' => $this->request->input('password'),
            'hostport' => $this->request->input('hostport'),
            'database' => '',
        ];

        $result = $this->connectDb($database);

        if ($result['code'] == 0) {
            return $this->error($result['msg']);
        }

        return $this->success('', ['databases' => $result['databases']]);
    }

    /**
     * 系统基础配置 - 优化版
     */
    public function baseConfig()
    {
        $envOk = $this->commandExecutionCheck();
        $rootPath = str_replace('\\', '/', base_path());

        if (request()->isGet()) {
            return $this->success('', [
                'rootPath' => $rootPath,
                'executionWebCommand' => $envOk
            ]);
        }

        return $this->handleDatabaseConfiguration();
    }

    /**
     * 处理数据库配置
     */
    private function handleDatabaseConfiguration(): Response
    {
        $databaseParam = request()->only(['hostname', 'username', 'password', 'hostport', 'database', 'prefix']);

        // 测试数据库连接
        if (!$this->testDatabaseConnection($databaseParam)) {
            return $this->error(__('Database connection failed'));
        }

        // 创建数据库
        $this->createDatabaseIfNotExists($databaseParam);

        // 写入配置文件
        $this->writeConfigurationFiles($databaseParam);

        // 生成新的Token密钥
        $this->generateNewTokenKey();

        return $this->success('', [
            'rootPath' => str_replace('\\', '/', base_path()),
            'executionWebCommand' => $this->commandExecutionCheck()
        ]);
    }

    /**
     * 测试数据库连接
     */
    private function testDatabaseConnection(array $databaseParam): bool
    {
        $connectData = $databaseParam;
        $connectData['database'] = '';

        $connect = $this->connectDb($connectData, true);
        return $connect['code'] != 0;
    }

    /**
     * 创建数据库（如果不存在）
     */
    private function createDatabaseIfNotExists(array $databaseParam): void
    {
        $connectData = $databaseParam;
        $connectData['database'] = '';

        $connect = $this->connectDb($connectData, true);
        $databases = $connect['databases'] ?? [];

        if (!in_array($databaseParam['database'], $databases)) {
            $sql = "CREATE DATABASE IF NOT EXISTS `{$databaseParam['database']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
            $connect['pdo']->exec($sql);
        }
    }

    /**
     * 写入配置文件
     */
    private function writeConfigurationFiles(array $databaseParam): void
    {
        $this->writeDatabaseConfig($databaseParam);
        $this->writeEnvFile($databaseParam);
    }

    /**
     * 写入数据库配置文件
     */
    private function writeDatabaseConfig(array $databaseParam): void
    {
        $dbConfigFile = base_path() . '/config/' . self::DB_CONFIG_FILE;
        $dbConfigContent = file_get_contents($dbConfigFile);

        if (!$dbConfigContent) {
            throw new Exception(__('File not found: %s', ['config/' . self::DB_CONFIG_FILE]));
        }

        $callback = function ($matches) use ($databaseParam) {
            $key = $matches[1];
            $value = $databaseParam[$key] ?? $matches[4];
            $envKey = ($key == 'hostport') ? 'HOSTPORT' : strtoupper($key);
            return "'{$key}' => env('MYSQL_{$envKey}', '{$value}'),";
        };

        $dbConfigText = preg_replace_callback(
            "/'(hostname|database|username|password|hostport|prefix)'(\s+)=>(\s+)env\('.*?',\s*'(.*?)'\),/",
            $callback,
            $dbConfigContent
        );

        if (file_put_contents($dbConfigFile, $dbConfigText) === false) {
            throw new Exception(__('File has no write permission: %s', ['config/' . self::DB_CONFIG_FILE]));
        }
    }

    /**
     * 写入环境变量文件 - 从模板文件复制优化版
     */
    private function writeEnvFile(array $databaseParam): void
    {
        $envExampleFile = base_path() . '/.env.example';
        $envFile = base_path() . '/.env';

        // 检查模板文件是否存在
        if (!is_file($envExampleFile)) {
            throw new Exception(__('Template file not found: %s', ['.env.example']));
        }

        // 读取模板文件内容
        $templateContent = file_get_contents($envExampleFile);
        if ($templateContent === false) {
            throw new Exception(__('Failed to read template file: %s', ['.env.example']));
        }

        // 替换数据库配置参数
        $replacements = [
            'MYSQL_HOSTNAME=' => 'MYSQL_HOSTNAME=' . $databaseParam['hostname'],
            'MYSQL_DATABASE=' => 'MYSQL_DATABASE=' . $databaseParam['database'],
            'MYSQL_USERNAME=' => 'MYSQL_USERNAME=' . $databaseParam['username'],
            'MYSQL_PASSWORD=' => 'MYSQL_PASSWORD=' . $databaseParam['password'],
            'MYSQL_HOSTPORT=' => 'MYSQL_HOSTPORT=' . $databaseParam['hostport'],
            'MYSQL_PREFIX=' => 'MYSQL_PREFIX=' . $databaseParam['prefix'],
        ];

        // 执行替换
        $envContent = $templateContent;
        foreach ($replacements as $search => $replace) {
            $envContent = preg_replace('/^' . preg_quote($search, '/') . '.*$/m', $replace, $envContent);
        }

        // 写入环境文件
        if (file_put_contents($envFile, $envContent) === false) {
            throw new Exception(__('File has no write permission: %s', ['/.env']));
        }
    }

    /**
     * 生成新的Token密钥
     */
    private function generateNewTokenKey(): void
    {
        $oldTokenKey = config('buildadmin.token.key');
        $newTokenKey = Random::build('alnum', 32);
        $buildConfigFile = base_path() . '/config/' . self::BUILD_CONFIG_FILE;
        $buildConfigContent = file_get_contents($buildConfigFile);

        $buildConfigContent = preg_replace(
            "/'key'(\s+)=>(\s+)'$oldTokenKey'/",
            "'key'\$1=>\$2'$newTokenKey'",
            $buildConfigContent
        );

        if (file_put_contents($buildConfigFile, $buildConfigContent) === false) {
            throw new Exception(__('File has no write permission:%s', ['/config/' . self::BUILD_CONFIG_FILE]));
        }
    }

    /**
     * 标记命令执行完毕 - 优化版
     */
    public function commandExecComplete(): Response
    {
        try {
            if ($this->isInstallComplete()) {
                return $this->error(__('The system has completed installation. If you need to reinstall, please delete the %s file first', ['public/' . self::LOCK_FILE]));
            }

            $param = $this->request->only(['type', 'adminname', 'adminpassword', 'sitename']);

            if ($param['type'] == 'web') {
                $this->createInstallLockFile();
            } else {
                $this->configureAdminSettings($param);
            }

            return $this->success();
        } catch (Throwable $e) {
            throw $e;
        }
    }

    /**
     * 创建安装锁文件
     */
    private function createInstallLockFile(): void
    {
        $result = file_put_contents(base_path() . '/public/' . self::LOCK_FILE, self::INSTALL_COMPLETE_MARK);
        if (!$result) {
            throw new Exception(__('File has no write permission:%s', ['public/' . self::LOCK_FILE]));
        }
    }

    /**
     * 配置管理员设置
     */
    private function configureAdminSettings(array $param): void
    {
        try {
            // 更新管理员信息
            $adminModel = new AdminModel();
            $defaultAdmin = $adminModel->where('username', 'admin')->find();
            if ($defaultAdmin) {
                $defaultAdmin->username = $param['adminname'];
                $defaultAdmin->nickname = ucfirst($param['adminname']);
                $defaultAdmin->save();

                if (!empty($param['adminpassword'])) {
                    $adminModel->resetPassword($defaultAdmin->id, $param['adminpassword']);
                }
            }

            // 更新默认用户密码
            $user = new UserModel();
            $user->resetPassword(1, Random::build());

            // 更新站点名称
            Config::where('name', 'site_name')->update([
                'value' => $param['sitename']
            ]);
        } catch (DbException $e) {
            throw new BusinessException($e->getMessage(), $e->getCode(), false, [], $e);
        }
    }

    /**
     * 检查安装是否完成
     */
    protected function isInstallComplete(): bool
    {
        $lockFile = public_path() . DIRECTORY_SEPARATOR . self::LOCK_FILE;

        if (is_file($lockFile)) {
            $contents = @file_get_contents($lockFile);
            // 打开event
            modify_config('app.php', ['enable' => true], "webman/event");
            return $contents == self::INSTALL_COMPLETE_MARK && config('plugin.webman.event.app.enable');
        }

        return false;
    }

    /**
     * 命令执行检查
     */
    private function commandExecutionCheck(): bool
    {
        $pm = config('terminal.npm_package_manager');
        if ($pm == 'none') {
            return false;
        }

        $checks = [
            'phpPopen' => function_exists('proc_open') && function_exists('proc_close'),
            'npmVersionCompare' => Version::compare(self::DEPENDENT_VERSIONS['npm'], Version::getVersion('npm')),
            'pmVersionCompare' => Version::compare(self::DEPENDENT_VERSIONS[$pm], Version::getVersion($pm)),
            'nodejsVersionCompare' => Version::compare(self::DEPENDENT_VERSIONS['node'], Version::getVersion('node')),
        ];

        return !in_array(false, $checks, true);
    }

    /**
     * 数据库连接
     */
    private function connectDb(array $database, bool $returnPdo = false): array
    {
        try {
            $dbConfig = config('think-orm');
            $dbConfig['connections']['mysql'] = array_merge($dbConfig['connections']['mysql'], $database);
            Db::setConfig($dbConfig);
            Db::connect('mysql');
            Db::execute("SELECT 1");
        } catch (PDOException $e) {
            return [
                'code' => 0,
                'msg' => __('Database connection failed:%s', [mb_convert_encoding($e->getMessage() ?: 'unknown', 'UTF-8', 'UTF-8,GBK,GB2312,BIG5')])
            ];
        }

        $databases = [];
        $excludeDatabases = ['information_schema', 'mysql', 'performance_schema', 'sys'];
        $res = Db::query("SHOW DATABASES");

        foreach ($res as $row) {
            if (!in_array($row['Database'], $excludeDatabases)) {
                $databases[] = $row['Database'];
            }
        }

        return [
            'code' => 1,
            'msg' => '',
            'databases' => $databases,
            'pdo' => $returnPdo ? Db::getPdo() : '',
        ];
    }

    /**
     * 手动安装指引
     */
    public function manualInstall()
    {
        return $this->success('', [
            'webPath' => str_replace('\\', '/', base_path() . '/web')
        ]);
    }

    /**
     * 移动前端文件
     */
    public function mvDist()
    {
        if (!is_file(base_path() . self::DIST_DIR . DIRECTORY_SEPARATOR . 'index.html')) {
            return $this->error(__('No built front-end file found, please rebuild manually!'));
        }

        $distDir = base_path() . self::DIST_DIR;
        $targetDir = public_path();

        try {
            $this->moveDirectory($distDir, $targetDir);
            return $this->success(__('Front-end files moved successfully!'));
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 移动目录内容
     */
    private function moveDirectory(string $source, string $target): void
    {
        if (!is_dir($source)) {
            throw new Exception(__('Source directory does not exist'));
        }

        if (!FileUtil::pathIsWritable($target)) {
            throw new Exception(__('Target directory is not writable'));
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $targetPath = $target . DIRECTORY_SEPARATOR . $iterator->getSubPathName();

            if ($item->isDir()) {
                if (!is_dir($targetPath)) {
                    mkdir($targetPath, 0755, true);
                }
            } else {
                copy($item->getPathname(), $targetPath);
            }
        }
    }

    /**
     * 获取可写状态描述
     */
    private static function writableStateDescribe($writable): string
    {
        return $writable ? __('Writable') : __('No write permission');
    }

    /**
     * 操作成功响应
     */
    private function success(string $msg = '', $data = null, int $code = 1): Response
    {
        return json([
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
            'time' => time()
        ]);
    }

    /**
     * 操作失败响应
     */
    private function error(string $msg = '', $data = null, int $code = 0): Response
    {
        return json([
            'code' => $code,
            'msg' => $msg,
            'data' => $data,
            'time' => time()
        ]);
    }
}