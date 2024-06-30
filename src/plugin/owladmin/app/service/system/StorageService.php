<?php

namespace plugin\owladmin\app\service\system;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use plugin\owladmin\app\model\system\Config;
use Illuminate\Database\Eloquent\Collection;
use plugin\owladmin\app\service\AdminService;

class StorageService extends AdminService
{
    public function saveConfig(array $data): bool
    {
        settings()->set('storage', $data);
        settings()->clearCache('storage');
        return true;
    }

    public function getEditData($id): Model|Collection|Builder|array|null
    {
        return settings()->get('storage');
    }
}
