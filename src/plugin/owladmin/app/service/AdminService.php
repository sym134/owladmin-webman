<?php

namespace plugin\owladmin\app\service;

use Throwable;
use support\Db as DB;
use support\Request;
use support\Container;
use Illuminate\Support\Arr;
use plugin\owladmin\app\renderer\Page;
use Illuminate\Database\Eloquent\Model;
use plugin\owladmin\app\trait\ErrorTrait;
use Illuminate\Database\Eloquent\Builder;
use plugin\owladmin\app\renderer\TableColumn;
use Illuminate\Database\Eloquent\Collection;

abstract class AdminService
{
    use ErrorTrait;

    protected array $tableColumn = [];

    protected string $modelName;

    protected Request|null $request;

    public function __construct()
    {
        $this->request = request();
    }

    public static function make(): static
    {
        return new static;
    }

    public function setModelName($modelName): void
    {
        $this->modelName = $modelName;
    }

    /**
     * @return Model
     */
    public function getModel(): Model
    {
        return new $this->modelName;
    }

    public function primaryKey(): string
    {
        return $this->getModel()->getKeyName();
    }

    public function getTableColumns(): array
    {
        if (!$this->tableColumn) {
            try {
                // laravel11: sqlite 暂时无法获取字段, 等待 laravel 适配
                $this->tableColumn = DB::schema($this->getModel()->getConnectionName())
                    ->getColumnListing($this->getModel()->getTable());
            } catch (Throwable $e) {
                $this->tableColumn = [];
            }
        }

        return $this->tableColumn;
    }

    public function hasColumn($column): bool
    {
        $columns = $this->getTableColumns();

        if (blank($columns)) return true;

        return in_array($column, $columns);
    }

    public function query()
    {
        return $this->modelName::query();
    }

    /**
     * 详情 获取数据
     *
     * @param $id
     *
     * @return Builder|Builder[]|Collection|Model|null
     */
    public function getDetail($id): Model|Collection|Builder|array|null
    {
        $query = $this->query();

        $this->addRelations($query, 'detail');

        return $query->find($id);
    }

    /**
     * 编辑 获取数据
     *
     * @param $id
     *
     * @return Model|Collection|Builder|array|null
     */
    public function getEditData($id): Model|Collection|Builder|array|null
    {
        $model = $this->getModel();

        $hidden = collect([$model->getCreatedAtColumn(), $model->getUpdatedAtColumn()])
            ->filter(fn($item) => $item !== null)
            ->toArray();

        $query = $this->query();

        $this->addRelations($query, 'edit');

        return $query->find($id)->makeHidden($hidden);
    }

    /**
     * 列表 获取查询
     *
     * @return Builder
     */
    public function listQuery(): Builder
    {
        $query = $this->query();

        // 处理排序
        $this->sortable($query);

        // 自动加载 TableColumn 内的关联关系
        $this->loadRelations($query);

        // 处理查询
        $this->searchable($query);

        // 追加关联关系
        $this->addRelations($query);

        return $query;
    }

    /**
     * 添加关联关系
     *
     * 预留钩子, 方便处理只需要添加 [关联] 的情况
     *
     * @param        $query
     * @param string $scene 场景: list, detail, edit
     *
     * @return void
     */
    public function addRelations($query, string $scene = 'list')
    {

    }

    /**
     * 根据 tableColumn 定义的列, 自动加载关联关系
     *
     * @param $query
     *
     * @return void
     */
    public function loadRelations($query): void
    {
        $controller = Container::make(request()->route->getCallback()[0], []);

        // 当前列表结构
        $schema = method_exists($controller, 'list') ? $controller->list() : '';

        if (!$schema instanceof Page) return;

        // 字段
        $columns = $schema->toArray()['body']->amisSchema['columns'] ?? [];

        $relations = [];
        foreach ($columns as $column) {
            // 排除非表格字段
            if (!$column instanceof TableColumn) continue;
            // 拆分字段名
            $field = $column->amisSchema['name'];
            // 是否是多层级
            if (str_contains($field, '.')) {
                // 去除字段名
                $list = array_slice(explode('.', $field), 0, -1);
                try {
                    $_class = $this->modelName;
                    foreach ($list as $item) {
                        $_class = appw($_class)->{$item}()->getModel()::class;
                    }
                } catch (Throwable $e) {
                    continue;
                }
                $relations[] = implode('.', $list);
            }
        }

        // 加载关联关系
        $query->with(array_unique($relations));
    }

    /**
     * 排序
     *
     * @param $query
     *
     * @return void
     */
    public function sortable($query): void
    {
        if (request()->input('orderBy') && request()->input('orderDir')) {
            $query->orderBy(request()->input('orderBy'), request()->input('orderDir') ?? 'asc');
        } else {
            $query->orderByDesc($this->sortColumn());
        }
    }

    /**
     * 搜索
     *
     * @param $query
     *
     * @return void
     */
    public function searchable($query): void
    {
        collect(array_keys(request()->all()))
            ->intersect($this->getTableColumns())
            ->map(function ($field) use ($query) {
                $query->when(request()->input($field), function ($query) use ($field) {
                    $query->where($field, 'like', '%' . request()->input($field) . '%');
                });
            });
    }

    /**
     * 列表 排序字段
     *
     * @return mixed
     */
    public function sortColumn(): mixed
    {
        $updatedAtColumn = $this->getModel()->getUpdatedAtColumn();

        if ($this->hasColumn($updatedAtColumn)) {
            return $updatedAtColumn;
        }

        if ($this->hasColumn($this->getModel()->getKeyName())) {
            return $this->getModel()->getKeyName();
        }

        return Arr::first($this->getTableColumns());
    }

    /**
     * 列表 获取数据
     *
     * @return array
     */
    public function list(): array
    {
        $query = $this->listQuery();

        $list = $query->paginate(request()->input('perPage', 20));
        $items = $list->items();
        $total = $list->total();

        return compact('items', 'total');
    }

    /**
     * 修改
     *
     * @param $primaryKey
     * @param $data
     *
     * @return bool
     */
    public function update($primaryKey, $data): bool
    {
        $this->saving($data, $primaryKey);

        $model = $this->query()->whereKey($primaryKey)->first();

        foreach ($data as $k => $v) {
            if (!$this->hasColumn($k)) {
                continue;
            }

            $model->setAttribute($k, $v);
        }

        $result = $model->save();

        if ($result) {
            $this->saved($model, true);
        }

        return $result;
    }

    /**
     * 新增
     *
     * @param $data
     *
     * @return bool
     */
    public function store($data): bool
    {
        $this->saving($data);

        $model = $this->getModel();

        foreach ($data as $k => $v) {
            if (!$this->hasColumn($k)) {
                continue;
            }

            $model->setAttribute($k, $v);
        }

        $result = $model->save();

        if ($result) {
            $this->saved($model);
        }

        return $result;
    }

    /**
     * 删除
     *
     * @param string $ids
     *
     * @return bool
     */
    public function delete(string $ids): bool
    {
        $result = $this->query()->whereIn($this->primaryKey(), explode(',', $ids))->delete();

        if ($result) {
            $this->deleted($ids);
        }

        return $result;
    }

    /**
     * 快速编辑
     *
     * @param $data
     *
     * @return true
     */
    public function quickEdit($data): bool
    {
        $rowsDiff = data_get($data, 'rowsDiff', []);

        foreach ($rowsDiff as $item) {
            $this->update(Arr::pull($item, $this->primaryKey()), $item);
        }

        return true;
    }

    /**
     * 快速编辑单条
     *
     * @param $data
     *
     * @return bool
     */
    public function quickEditItem($data): bool
    {
        return $this->update(Arr::pull($data, $this->primaryKey()), $data);
    }

    /**
     * saving 钩子 (执行于新增/修改前)
     *
     * 可以通过判断 $primaryKey 是否存在来判断是新增还是修改
     *
     * @param        $data
     * @param string $primaryKey
     *
     * @return void
     */
    public function saving(&$data, string $primaryKey = '')
    {

    }

    /**
     * saved 钩子 (执行于新增/修改后)
     *
     * 可以通过 $isEdit 来判断是新增还是修改
     *
     * @param      $model
     * @param bool $isEdit
     *
     * @return void
     */
    public function saved($model, bool $isEdit = false)
    {

    }

    /**
     * deleted 钩子 (执行于删除后)
     *
     * @param $ids
     *
     * @return void
     */
    public function deleted($ids)
    {

    }
}
