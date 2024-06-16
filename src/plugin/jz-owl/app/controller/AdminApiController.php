<?php

namespace plugin\jzadmin\app\controller;

use support\Response;
use Illuminate\Support\Str;
use plugin\jzadmin\app\Admin;
use plugin\jzadmin\app\service\AdminApiService;

/**
 * @property AdminApiService $service
 */
class AdminApiController extends AdminController
{
    public string $serviceName = AdminApiService::class;

    public function index(): Response
    {
        $path = Str::of(request()->path())->replace(Admin::config('admin.route.prefix'), '')->value();
        $api  = $this->service->getApiByPath($path);

        if (!$api) {
            return $this->response()->success();
        }

        return appw($api->template)->handle();
    }
}
