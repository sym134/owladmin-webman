<?php

namespace plugin\owladmin\app\service\monitor;

use plugin\owladmin\app\service\AdminService;
use plugin\owladmin\app\model\monitor\AdminOperationLog;

class AdminOperationLogService extends AdminService
{
    protected string $modelName = AdminOperationLog::class;

    public function searchable($query): void
    {
        collect(array_keys(request()->all()))
            ->intersect($this->getTableColumns())
            ->map(function ($field) use ($query) {
                $query->when(request()->input($field), function ($query) use ($field) {
                    if ($field === 'created_at') {
                        $created_at = explode(',', request()->input($field));
                        $query->whereBetween($field, [$created_at[0], $created_at[1]]);
                    } else {
                        $query->where($field, 'like', '%' . request()->input($field) . '%');
                    }
                });
            });
    }
}
