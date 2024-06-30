<?php

namespace plugin\owladmin\app\controller\system;

use support\Response;
use plugin\owladmin\app\renderer\Page;
use plugin\owladmin\app\model\system\Attachment;
use plugin\owladmin\app\controller\AdminController;
use plugin\owladmin\app\service\system\AttachmentService;

class AttachmentController extends AdminController
{
    protected string $serviceName = AttachmentService::class;

    // public function index(): Response
    // {
    //     return $this->response()->success($this->form());
    // }

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
                    ->set('type', 'mapping')->set('map', Attachment::STORAGE_MODE),
                amis()->TableColumn('origin_name', admin_trans('admin.admin_attachments.origin_name')),
                amis()->TableColumn('new_name', admin_trans('admin.admin_attachments.new_name')),
                amis()->TableColumn('mime_type', admin_trans('admin.admin_attachments.mime_type')),
                amis()->TableColumn('storage_path', admin_trans('admin.admin_attachments.storage_path')),
                amis()->Tpl()->name('file_size')->label(admin_trans('admin.admin_attachments.file_size'))->tpl('${round(file_size/1024)}'.'MB'),

                $this->rowActions([
                    // $this->rowEditButton(true),
                    $this->rowDeleteButton(),
                ]),
            ]);

        return $this->baseList($crud);
    }


    public function form()
    {
        return $this->baseForm()
            ->body();
    }

    public function form3(): \plugin\owladmin\app\renderer\Form
    {
        var_dump(111);
        return $this->baseForm()
            ->body(
                amis()->Grid()->columns([
                    ['body' => [
                        amis()->TreeControl()->id('u:838d81c2093b')->label('树组件')->name('tree')->options([
                            [
                                'label' => '所有文件',
                                'value' => '',
                            ],
                            [
                                'label' => '图片',
                                'value' => 'image',
                            ],
                            [
                                'label' => '文档',
                                'value' => 'text',
                            ],
                            [
                                'label' => '音频',
                                'value' => 'audio',
                            ],
                            [
                                'label' => '文件',
                                'value' => 'file',
                            ],
                        ])->asideResizor('')->pullRefresh([
                            'disabled' => '1',
                        ]),
                    ], 'md' => 2],
                    ['body' => [
                        $this->baseList($this->baseCRUD()
                            ->headerToolbar([
                                // $this->createButton(true),
                                ...$this->baseHeaderToolBar(),
                            ])
                            ->filterDefaultVisible(true)
                            ->filter($this->baseFilter()->body([
                                amis()->TextControl('origin_name', admin_trans('admin.admin_attachments.origin_name'))
                                    ->size('md'),
                                amis()->SelectControl('storage_mode', admin_trans('admin.admin_attachments.storage_mode'))
                                    ->size('md')->options(Attachment::STORAGE_MODE),
                            ]))
                            ->columns([
                                amis()->TableColumn('id', 'ID'),
                                amis()->TableColumn('storage_mode', admin_trans('admin.admin_attachments.storage_mode')),
                                amis()->TableColumn('origin_name', admin_trans('admin.admin_attachments.origin_name')),
                                amis()->TableColumn('new_name', admin_trans('admin.admin_attachments.new_name')),
                                amis()->TableColumn('mime_type', admin_trans('admin.admin_attachments.mime_type')),
                                amis()->TableColumn('storage_path', admin_trans('admin.admin_attachments.storage_path')),
                                amis()->TableColumn('file_size', admin_trans('admin.admin_attachments.file_size')),
                                amis()->TableColumn('url', admin_trans('admin.admin_attachments.url')),

                                $this->rowActions([
                                    // $this->rowEditButton(true),
                                    $this->rowDeleteButton(),
                                ]),
                            ])
                        ),
                    ]],
                ])
            );
    }
}
