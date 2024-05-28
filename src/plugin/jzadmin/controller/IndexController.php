<?php

namespace plugin\jzadmin\controller;

use support\Response;
use support\Request;
use Illuminate\Http\JsonResponse;
use plugin\jzadmin\Admin;
use plugin\jzadmin\model\Extension;

class IndexController extends AdminController
{
    public function menus()
    {
        return $this->response()->success(Admin::menu()->all());
    }

    public function noContentResponse(): Response
    {
        return $this->response()->successMessage();
    }

    public function settings(): Response|JsonResponse
    {
        return $this->response()->success([
            'nav'      => Admin::getNav(),
            'assets'   => Admin::getAssets(),
            'app_name' => Admin::config('admin.name'),
            'locale'   => config('app.locale'),
            'layout'   => Admin::config('admin.layout'),
            'logo'     => url(Admin::config('admin.logo')),

            'login_captcha'          => Admin::config('admin.auth.login_captcha'),
            'show_development_tools' => Admin::config('admin.show_development_tools'),
            'system_theme_setting'   => Admin::setting()->get('system_theme_setting'),
            'enabled_extensions'     => Extension::query()->where('is_enabled', 1)->pluck('name')?->toArray(),
        ]);
    }

    /**
     * 保存设置项
     *
     * @param Request $request
     *
     * @return Response
     */
    public function saveSettings(Request $request)
    {
        Admin::setting()->setMany($request->all());

        return $this->response()->successMessage();
    }

    /**
     * 下载导出文件
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadExport(Request $request)
    {
        return response()->download(storage_path('app/' . $request->input('path')))->deleteFileAfterSend();
    }
}
