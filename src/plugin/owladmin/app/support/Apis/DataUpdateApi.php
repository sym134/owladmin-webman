<?php

namespace plugin\owladmin\app\support\Apis;

use support\Response;
use plugin\owladmin\app\Admin;
use plugin\owladmin\app\service\AdminService;

/**
 * 数据更新
 */
class DataUpdateApi extends AdminBaseApi
{
    public string $method = 'put';

    public function getTitle(): string
    {
        return admin_trans('admin.api_templates.data_update');
    }

    public function handle(): Response
    {
        $result = $this->service()->update(request()->input($this->getArgs('primary_key', 'id')), request()->all());

        if ($result) {
            return Admin::response()
                ->successMessage(admin_trans('admin.successfully_message', ['attribute' => admin_trans('admin.save')]));
        }

        return Admin::response()->fail(admin_trans('admin.failed_message', ['attribute' => admin_trans('admin.save')]));
    }

    public function argsSchema(): array
    {
        return [
            amis()->SelectControl('model', admin_trans('admin.relationships.model'))
                ->required()
                ->menuTpl('${label} <span class="text-gray-300 pl-2">${table}</span>')
                ->source('/dev_tools/relation/model_options')
                ->searchable(),
            amis()->TextControl('primary_id', admin_trans('admin.code_generators.primary_key'))->value('id'),
        ];
    }

    protected function service(): AdminService
    {
        $service = $this->blankService();

        $service->setModelName($this->getArgs('model'));

        return $service;
    }
}
