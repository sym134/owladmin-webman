<?php

namespace plugin\owladmin\app\controller\system;

use plugin\owladmin\app\renderer\Page;
use plugin\owladmin\app\controller\AdminController;
use plugin\owladmin\app\service\system\AdminCrontabLogService;

/**
 * 定时任务日志
 *
 * @property AdminCrontabLogService $service
 */
class AdminCrontabLogController extends AdminController
{
    protected string $serviceName = AdminCrontabLogService::class;

    public function list($api = null): Page
    {
        $this->queryPath = '/system/admin_crontab_log';
        $crud = $this->baseCRUD()->api(is_null($api) ? $this->getListGetDataPath() : $api)
            ->filterTogglable(false)
            ->headerToolbar([
                ...$this->baseHeaderToolBar(),
            ])
            ->filter($this->baseFilter()->submitOnChange()->actions()->body([
                amis()->SelectControl('execution_status', '执行状态')->options([
                    1 => '成功', 2 => '失败',
                ]),
            ]))
            ->columns([
                amis()->TableColumn('id', 'ID')->sortable(),
                amis()->TableColumn('crontab_id', admin_trans('crontab.crontab_log.crontab_id')),
                amis()->TableColumn('target', admin_trans('crontab.crontab_log.target')),
                amis()->Json()->name('parameter'),
                amis()->TableColumn('exception_info', admin_trans('crontab.crontab_log.exception_info')),
                amis()->TableColumn('execution_status', admin_trans('crontab.crontab_log.execution_status'))->type('status')->map([1 => 'success', 2 => 'fail'])->labelMap([1 => '成功', 2 => '失败']),
                amis()->TableColumn('created_at', admin_trans('admin.created_at'))->sortable(),
                $this->rowActions([
                    $this->rowShowButton(true),

                    $this->rowDeleteButton(),
                ]),
            ]);

        return $this->baseList($crud);
    }

    public function detail()
    {
        return $this->baseDetail()->body([
            amis()->TextControl('id', 'ID')->static(),
            amis()->TextControl('crontab_id', admin_trans('crontab.crontab_log.crontab_id'))->static(),
            amis()->TextControl('target', admin_trans('crontab.crontab_log.target'))->static(),
            amis()->InputGroupControl()->label(admin_trans('crontab.crontab_log.parameter'))->body([
                amis()->Json()->name('parameter'),
            ]),
            amis()->TextControl('exception_info', admin_trans('crontab.crontab_log.exception_info'))->static(),
            amis()->TextControl('execution_status', admin_trans('crontab.crontab_log.execution_status'))->static(),
        ]);
    }
}
