<?php

namespace plugin\jzadmin\controller;

use plugin\jzadmin\Admin;
use Illuminate\Support\Str;
use plugin\jzadmin\service\AdminApiService;

/**
 * @property AdminApiService $service
 */
class AdminApiController extends AdminController
{
    public string $serviceName = AdminApiService::class;

    public function index()
    {
        $path = Str::of(request()->path())->replace(Admin::config('admin.route.prefix'), '')->value();
        $api  = $this->service->getApiByPath($path);

        if (!$api) {
            return $this->response()->success();
        }

        return app($api->template)->handle();
    }
}
