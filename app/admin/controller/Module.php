<?php


namespace app\admin\controller;

use app\admin\library\module\Manage;
use app\admin\library\module\Server;
use app\admin\model\AdminLog;
use app\common\controller\Backend;
use app\exception\ModuleException;
use support\Response;
use Throwable;

class Module extends Backend
{
    protected array $noNeedPermission = ['state', 'dependentInstallComplete'];

    public function initialize(): void
    {
        parent::initialize();
    }

    public function index(): Response
    {
        return $this->success('', [
            'sysVersion' => config('buildadmin.version'),
            'installed'  => Server::installedList(root_path() . 'modules' . DIRECTORY_SEPARATOR),
        ]);
    }

    public function state(): Response
    {
        $uid = $this->request->input("uid", '');
        if (!$uid) {
            return $this->error(__('Parameter error'));
        }
        return $this->success('', [
            'state' => Manage::instance($uid)->getInstallState()
        ]);
    }

    public function install(): Response
    {
        AdminLog::instance()->setTitle(__('Install module'));
        $uid     = $this->request->input("uid", '');
        $token   = $this->request->input("token", '');
        $orderId = $this->request->input("orderId", 0);
        if (!$uid) {
            return $this->error(__('Parameter error'));
        }
        $res = [];
        try {
            $res = Manage::instance($uid)->install($token, $orderId);
        } catch (ModuleException $e) {
            return $this->error(__($e->getMessage()), $e->getData(), $e->getCode());
        } catch (Throwable $e) {
            return $this->error(__($e->getMessage()));
        }
        return $this->success('', [
            'data' => $res,
        ]);
    }

    public function dependentInstallComplete(): Response
    {
        $uid = $this->request->input("uid", '');
        if (!$uid) {
            return $this->error(__('Parameter error'));
        }
        try {
            Manage::instance($uid)->dependentInstallComplete('all');
        } catch (ModuleException $e) {
            return $this->error(__($e->getMessage()), $e->getData(), $e->getCode());
        } catch (Throwable $e) {
            return $this->error(__($e->getMessage()));
        }
        return $this->success();
    }

    public function changeState(): Response
    {
        AdminLog::instance()->setTitle(__('Change module state'));
        $uid   = $this->request->input("uid/s", '');
        $state = $this->request->input("state/b", false);
        if (!$uid) {
            return $this->error(__('Parameter error'));
        }
        $info = [];
        try {
            $info = Manage::instance($uid)->changeState($state);
        } catch (ModuleException $e) {
            return $this->error(__($e->getMessage()), $e->getData(), $e->getCode());
        } catch (Throwable $e) {
            return $this->error(__($e->getMessage()));
        }
        return $this->success('', [
            'info' => $info,
        ]);
    }

    public function uninstall(): Response
    {
        AdminLog::instance()->setTitle(__('Unload module'));
        $uid = $this->request->input("uid", '');
        if (!$uid) {
            return $this->error(__('Parameter error'));
        }
        try {
            Manage::instance($uid)->uninstall();
        } catch (ModuleException $e) {
            return $this->error(__($e->getMessage()), $e->getData(), $e->getCode());
        } catch (Throwable $e) {
            return $this->error(__($e->getMessage()));
        }
        return $this->success();
    }

    public function update(): Response
    {
        AdminLog::instance()->setTitle(__('Update module'));
        $uid     = $this->request->input("uid/s", '');
        $token   = $this->request->input("token/s", '');
        $orderId = $this->request->input("orderId/d", 0);
        if (!$token || !$uid) {
            return $this->error(__('Parameter error'));
        }
        try {
            Manage::instance($uid)->update($token, $orderId);
        } catch (ModuleException $e) {
            return $this->error(__($e->getMessage()), $e->getData(), $e->getCode());
        } catch (Throwable $e) {
            return $this->error(__($e->getMessage()));
        }
        return $this->success();
    }

    public function upload(): Response
    {
        AdminLog::instance()->setTitle(__('Upload install module'));
        $file  = $this->request->input("file/s", '');
        $token = $this->request->input("token/s", '');
        if (!$file) $this->error(__('Parameter error'));
        if (!$token) $this->error(__('Please login to the official website account first'));

        $info = [];
        try {
            $info = Manage::instance()->upload($token, $file);
        } catch (ModuleException $e) {
            return $this->error(__($e->getMessage()), $e->getData(), $e->getCode());
        } catch (Throwable $e) {
            return $this->error(__($e->getMessage()));
        }
        return $this->success('', [
            'info' => $info
        ]);
    }
}