<?php

namespace plugin\owladmin\app\controller\monitor;

use plugin\owladmin\app\renderer\Page;
use plugin\owladmin\app\renderer\Form;
use plugin\owladmin\app\controller\AdminController;
use plugin\owladmin\app\service\monitor\AdminOperationLogService;

class AdminOperationLogController extends AdminController
{
    protected string $serviceName = AdminOperationLogService::class;

    public function list(): Page
    {
        $crud = $this->baseCRUD()
            ->headerToolbar([
                ...$this->baseHeaderToolBar(),
            ])
            ->filterDefaultVisible(true)
            ->filter(
                $this->baseFilter()->body([
                    amis()->TextControl('username', admin_trans('admin.admin_operation_log.username'))
                        ->size('md'),
                    amis()->TextControl('service_name', admin_trans('admin.admin_operation_log.service_name'))
                        ->size('md'),
                    amis()->TextControl('ip', admin_trans('admin.admin_operation_log.ip')),
                    amis()->InputDatetimeRange()->name('created_at')->label(admin_trans('admin.created_at'))
                        ->valueFormat('YYYY-MM-DD HH:mm:ss'),
                ])
            )
            ->columns([
                amis()->TableColumn('id', 'ID'),
                amis()->TableColumn('username', admin_trans('admin.admin_operation_log.username')),
                amis()->TableColumn('app', admin_trans('admin.admin_operation_log.app')),
                amis()->TableColumn('service_name', admin_trans('admin.admin_operation_log.service_name')),
                amis()->TableColumn('router', admin_trans('admin.admin_operation_log.router')),
                amis()->TableColumn('ip', admin_trans('admin.admin_operation_log.ip')),
                amis()->TableColumn('ip_location', admin_trans('admin.admin_operation_log.ip_location')),
                amis()->TableColumn('created_at', admin_trans('admin.created_at'))->type('datetime')->sortable(true),


                $this->rowActions([]),
            ]);

        return $this->baseList($crud);
    }

    public function form(): Form
    {
        return $this->baseForm()
            ->body();
    }
}
