<?php

namespace plugin\owladmin\app\service;

use plugin\owladmin\app\model\AdminPage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method AdminPage getModel()
 * @method AdminPage|Builder query()
 */
class AdminPageService extends AdminService
{
    protected string $modelName = AdminPage::class;

    public string $cacheKeyPrefix = 'admin_page-'; // webman 缓存不允许:

    public function saving(&$data, $primaryKey = ''): void
    {
        $data['schema'] = data_get($data, 'page.schema');
        admin_abort_if(blank($data['schema']), admin_trans('admin.pages.schema_cannot_be_empty'));
        unset($data['page']);

        $exists = $this->query()
            ->where('sign', $data['sign'])
            ->when($primaryKey, fn($q) => $q->where('id', '<>', $primaryKey))
            ->exists();

        admin_abort_if($exists, admin_trans('admin.pages.sign_exists'));
    }

    public function saved($model, $isEdit = false): void
    {
        if ($isEdit) {
            cache()->delete($this->cacheKeyPrefix . $model->sign);
        }
    }

    public function delete(string $ids): bool
    {
        $this->query()->whereIn('id', explode(',', $ids))->get()->map(function ($item) {
            cache()->delete($this->cacheKeyPrefix . $item->sign);
        });


        return parent::delete($ids);
    }

    public function getEditData($id): Model|Collection|Builder|array|null
    {
        $data = parent::getEditData($id);

        $data->setAttribute('page', ['schema' => $data->schema]);
        $data->setAttribute('schema', '');

        return $data;
    }

    /**
     * 获取页面结构
     *
     * @param $sign
     *
     * @return mixed
     */
    public function get($sign): mixed
    {
        return cache()->rememberForever($this->cacheKeyPrefix . $sign, function () use ($sign) {
            return $this->query()->where('sign', $sign)->value('schema');
        });
    }

    public function options(): Collection|array
    {
        return $this->query()->get(['sign as value', 'title as label']);
    }
}
