<?php

namespace plugin\owladmin\app\controller\system;

use support\Response;
use plugin\owladmin\app\renderer\Page;
use plugin\owladmin\app\renderer\Form;
use plugin\owladmin\app\model\system\Attachment;
use plugin\owladmin\app\controller\AdminController;
use plugin\owladmin\app\service\system\AttachmentService;

class AttachmentController extends AdminController
{
    protected string $serviceName = AttachmentService::class;

    public function list(): Page
    {
        $crud = $this->baseCRUD()
            ->headerToolbar([
                // $this->createButton(true),
                ...$this->baseHeaderToolBar(),
            ])
            ->filterDefaultVisible(true)
            ->filter(
                $this->baseFilter()->submitOnChange()->body([
                    amis()->SelectControl('file_type', admin_trans('admin.admin_attachments.file_type'))
                        ->size('md')->options(Attachment::FILE_TYPE),
                    amis()->TextControl('origin_name', admin_trans('admin.admin_attachments.origin_name'))
                        ->size('md'),
                    amis()->SelectControl('storage_mode', admin_trans('admin.admin_attachments.storage_mode'))
                        ->size('md')->options(Attachment::STORAGE_MODE),
                ])->actions()
            )
            ->columns([
                amis()->TableColumn('id', 'ID'),
                amis()->Image()->label('预览')->name('url')->enlargeAble()->width(70),
                amis()->TableColumn('storage_mode', admin_trans('admin.admin_attachments.storage_mode'))
                    ->type('mapping')->map(Attachment::STORAGE_MODE),
                amis()->TableColumn('origin_name', admin_trans('admin.admin_attachments.origin_name')),
                amis()->TableColumn('new_name', admin_trans('admin.admin_attachments.new_name')),
                amis()->TableColumn('mime_type', admin_trans('admin.admin_attachments.mime_type')),
                amis()->TableColumn('storage_path', admin_trans('admin.admin_attachments.storage_path')),
                amis()->TableColumn('file_size', admin_trans('admin.admin_attachments.file_size'))->type('tpl')->tpl('${round(file_size/1024)}' . 'MB'),
                amis()->TableColumn('created_at', admin_trans('admin.created_at'))->type('datetime')->sortable(true),
                $this->rowActions([
                    // $this->rowEditButton(true),
                    $this->rowDeleteButton(),
                ]),
            ]);

        return $this->baseList($crud);
    }

    public function form(): Form
    {
        return $this->baseForm()
            ->body();
    }
}
