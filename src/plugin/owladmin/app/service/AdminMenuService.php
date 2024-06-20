<?php

namespace plugin\owladmin\app\service;

use Illuminate\Support\Arr;
use plugin\owladmin\app\Admin;
use plugin\owladmin\app\model\AdminMenu;
use Illuminate\Database\Eloquent\Builder;
use support\Db as DB;

/**
 * @method AdminMenu getModel()
 * @method AdminMenu|Builder query()
 */
class AdminMenuService extends AdminService
{
    public function __construct()
    {
        parent::__construct();

        $this->modelName = Admin::adminMenuModel();
    }

    public function getTree(): array
    {
        $list = $this->query()->orderBy('order')->get()->toArray();

        return array2tree($list);
    }

    public function parentIsChild($id, $parent_id): bool
    {
        $parent = $this->query()->find($parent_id);

        do {
            if ($parent->parent_id == $id) {
                return true;
            }
            // 如果没有parent 则为顶级菜单 退出循环
            $parent = $parent->parent;
        } while ($parent);

        return false;
    }

    public function update($primaryKey, $data): bool
    {
        $columns = $this->getTableColumns();

        $parent_id = Arr::get($data, 'parent_id');
        if ($parent_id != 0) {
            amis_abort_if($this->parentIsChild($primaryKey, $parent_id), admin_trans('admin.admin_menu.parent_id_not_allow'));
        }

        $model = $this->query()->whereKey($primaryKey)->first();

        $data['id'] = $primaryKey;

        return $this->saveData($data, $columns, $model);
    }

    public function store($data): bool
    {
        $columns = $this->getTableColumns();
        $model = $this->getModel();
        return $this->saveData($data, $columns, $model);
    }

    public function changeHomePage($excludeId = 0): void
    {
        $this->query()->when($excludeId, fn($query) => $query->where('id', '<>', $excludeId))->update(['is_home' => 0]);
    }

    public function list(): array
    {
        return ['items' => $this->getTree()];
    }

    /**
     * @param           $data
     * @param array     $columns
     * @param AdminMenu $model
     *
     * @return bool
     */
    protected function saveData($data, array $columns, AdminMenu $model): bool
    {
        $urlExists = $this->query()
            ->where('url', data_get($data, 'url'))
            ->when(data_get($data, 'id'), fn($q) => $q->where('id', '<>', data_get($data, 'id')))
            ->exists();

        admin_abort_if($urlExists, admin_trans('admin.admin_menu.url_exists'));

        foreach ($data as $k => $v) {
            if (!in_array($k, $columns)) {
                continue;
            }

            $v = $k == 'parent_id' ? intval($v) : $v;

            $model->setAttribute($k, $v);

            if ($k == 'is_home' && $v == 1) {
                $this->changeHomePage($model->getKey());
            }
        }
        return $model->save();
    }

    /**
     * 重新排序菜单
     *
     * @param $ids
     *
     * @return false|int
     */
    public function reorder($ids): bool|int
    {
        if (blank($ids)) {
            return false;
        }

        $ids = json_decode('[' . str_replace('[', ',[', $ids) . ']');

        $list = collect($this->refreshOrder($ids))->transform(fn($i) => $i * 10)->all();

        $sql = 'update ' . $this->getModel()->getTable() . ' set `order` = case id ';

        foreach ($list as $k => $v) {
            $sql .= " when {$k} then {$v} ";
        }

        return DB::update($sql . ' else `order` end');
    }

    public function refreshOrder($list)
    {
        $result = collect($list)->filter(fn($i) => !is_array($i))->values()->flip()->toArray();

        collect($list)->filter(fn($i) => is_array($i))->each(function ($item) use (&$result) {
            $result = $this->refreshOrder($item) + $result;
        });

        return $result;
    }
}
