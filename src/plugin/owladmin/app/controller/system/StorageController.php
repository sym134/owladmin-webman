<?php

namespace plugin\owladmin\app\controller\system;

use support\Request;
use support\Response;
use plugin\owladmin\app\renderer\Form;
use plugin\owladmin\app\controller\AdminController;
use plugin\owladmin\app\service\system\ConfigService;
use plugin\owladmin\app\service\system\StorageService;

class StorageController extends AdminController
{
    protected string $serviceName = StorageService::class;

    public function index(): Response
    {
        $this->isEdit = true;
        if ($this->actionOfGetData()) {
            return $this->response()->success($this->service->getEditData(0));
        }
        $form = amis()->Wrapper()->className('')->body([
            amis()->Card()->body('<div class="bg-yellow-100 text-yellow-600 p-2">⚠ 温馨提示：1.切换存储方式后，需要将资源文件传输至新的存储端；2.请勿随意切换存储方式，可能导致图片无法查看</div>'),
            amis()
                ->Card()
                ->className('base-form')
                ->header(['title' => '存储设置'])
                // ->toolbar([$this->backButton()])
                ->body(
                    [
                        $this->form(true)->api('put:' . admin_url($this->queryPath . '/update'))->initApi(admin_url($this->queryPath . '?_action=getData')),
                    ]
                ),
        ]);

        $page = $this->basePage()->body($form);

        return $this->response()->success($page);
    }

    public function form(): Form
    {
        return $this->baseForm(false)
            ->panelClassName('px-10 m:px-0')->mode('horizontal')
            ->body([
                amis()->Wrapper()->body([
                    amis()->SelectControl('engine', '存储状态')
                        ->options(['local' => '本地存储', 'qiniu' => '七牛云存储', 'aliyun' => '阿里云存储', 'qcloud' => '腾讯云存储']),
                    amis()->TextControl('upload_size', '上传大小')->value('5242880')->description('单位Byte,1MB=1024*1024Byte'),
                    amis()->TextControl('file_type', '文件类型')->value('txt,doc,docx,xls,xlsx,ppt,pptx,rar,zip,7z,gz,pdf,wps,md'),
                    amis()->TextControl('image_type', '图片类型')->value('jpg,jpeg,png,gif,svg,bmp'),
                ]),
                amis()->Wrapper()->visibleOn('engine==\'local\'')->body([
                    amis()->TextControl('local.path', '本地存储路径')->required(),
                    amis()->TextControl('local.domain', '域名')->validations(['isUrl' => true])->description('请补全http://或https://，例如https://zzz.xxx.com')->required(),
                ]),
                amis()->Wrapper()->visibleOn('engine==\'qiniu\'')->body([
                    amis()->TextControl('qiniu.bucket', '存储空间')->required(),
                    amis()->TextControl('qiniu.access_key', 'AccessKey')->required(),
                    amis()->TextControl('qiniu.secret_key', 'SecretKey')->required(),
                    amis()->TextControl('qiniu.domain', '域名')->description('请补全http://或https://，例如https://zzz.xxx.com'),
                ]),
                amis()->Wrapper()->visibleOn('engine==\'aliyun\'')->body([
                    amis()->TextControl('aliyun.bucket', '存储空间')->required(),
                    amis()->TextControl('aliyun.access_key', 'AccessKey')->required(),
                    amis()->TextControl('aliyun.secret_key', 'SecretKey')->required(),
                    amis()->TextControl('aliyun.domain', '域名')->description('请补全http://或https://，例如https://zzz.xxx.com'),
                ]),
                amis()->Wrapper()->visibleOn('engine==\'qcloud\'')->body([
                    amis()->TextControl('qcloud.bucket', '存储空间')->required(),
                    amis()->TextControl('qcloud.access_key', 'AccessKey')->required(),
                    amis()->TextControl('qcloud.secret_key', 'SecretKey')->required(),
                    amis()->TextControl('qcloud.domain', '域名')->description('请补全http://或https://，例如https://zzz.xxx.com'),
                    amis()->TextControl('qcloud.region', 'REGION')->required(),
                ]),
                // amis()->TextControl('site_name', '网站名称'),
                // amis()->TextControl('site_keywords', '网站关键字'),
                // amis()->TextControl('site_desc', '网站描述'),
                // amis()->TextControl('site_copyright', '版权信息'),
                // amis()->TextControl('site_record_number', '网站备案号'),

            ]);
    }

    public function update(Request $request): Response
    {
        $response = fn($result) => $this->autoResponse($result, admin_trans('admin.save'));
        return $response($this->service->saveConfig($request->all()));
    }
}
