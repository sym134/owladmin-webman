<?php

namespace plugin\owladmin\app\controller;

use plugin\owladmin\app\renderer\Page;
use plugin\owladmin\app\renderer\Form;
use plugin\owladmin\app\service\AdminUserService;
use plugin\owladmin\app\service\AdminRoleService;

/**
 * @property AdminUserService $service
 */
class AdminUserController extends AdminController
{
    protected string $serviceName = AdminUserService::class;

    public function list(): Page
    {
        $crud = $this->baseCRUD()
            ->headerToolbar([
                $this->createButton(true),
                ...$this->baseHeaderToolBar(),
            ])
            ->filter($this->baseFilter()->body(
                amis()->TextControl('keyword', admin_trans('admin.keyword'))
                    ->size('md')
                    ->placeholder(admin_trans('admin.admin_user.search_username'))
            ))
            ->itemCheckableOn('${!administrator}')
            ->columns([
                amis()->TableColumn('id', 'ID')->sortable(),
                amis()->TableColumn('avatar', admin_trans('admin.admin_user.avatar'))->type('avatar')->src('${avatar}'),
                amis()->TableColumn('username', admin_trans('admin.username')),
                amis()->TableColumn('name', admin_trans('admin.admin_user.name')),
                amis()->TableColumn('roles', admin_trans('admin.admin_user.roles'))->type('each')->items(
                    amis()->Tag()->label('${name}')->className('my-1')
                ),
                amis()->TableColumn('enabled', admin_trans('admin.extensions.card.status'))->quickEdit(
                    amis()->SwitchControl()->mode('inline')->disabledOn('${administrator}')->saveImmediately(true)
                ),
                amis()->TableColumn('created_at', admin_trans('admin.created_at'))->type('datetime')->sortable(true),
                $this->rowActions([
                    $this->rowEditButton(true),
                    $this->rowDeleteButton()->hiddenOn('${administrator}'),
                ]),
            ]);

        return $this->baseList($crud);
    }

    public function form(): Form
    {
        return $this->baseForm()->body([
            amis()->ImageControl('avatar', admin_trans('admin.admin_user.avatar'))->receiver($this->uploadImagePath()),
            amis()->TextControl('username', admin_trans('admin.username'))->required(),
            amis()->TextControl('name', admin_trans('admin.admin_user.name'))->required(),
            amis()->TextControl('password', admin_trans('admin.password'))->type('input-password'),
            amis()->TextControl('confirm_password', admin_trans('admin.confirm_password'))->type('input-password'),
            amis()->SelectControl('roles', admin_trans('admin.admin_user.roles'))
                ->searchable()
                ->multiple()
                ->labelField('name')
                ->valueField('id')
                ->joinValues(false)
                ->extractValue()
                ->options(AdminRoleService::make()->query()->get(['id', 'name'])),
            amis()->SwitchControl('enabled', admin_trans('admin.extensions.card.status'))
                ->onText(admin_trans('admin.extensions.enable'))
                ->offText(admin_trans('admin.extensions.disable'))
                ->disabledOn('${id == 1}')
                ->value(1),
        ]);
    }

    public function detail(): Form
    {
        return $this->baseDetail()->body([]);
    }
}
