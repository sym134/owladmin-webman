<?php

namespace plugin\jzadmin\app\service;

use yzh52521\hash\Hash;
use Illuminate\Support\Arr;
use plugin\jzadmin\app\Admin;
use plugin\jzadmin\facade\Crypt;
use plugin\jzadmin\app\model\AdminUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method AdminUser getModel()
 * @method AdminUser|Builder query()
 */
class AdminUserService extends AdminService
{
    public function __construct()
    {
        parent::__construct();

        $this->modelName = Admin::adminUserModel();
    }

    public function getEditData($id): Model|Collection|Builder|array|null
    {
        $adminUser = parent::getEditData($id)->makeHidden('password');

        $adminUser->load('roles');

        return $adminUser;
    }

    public function store($data): bool
    {
        $this->checkUsernameUnique($data['username']);

        admin_abort_if(!data_get($data, 'password'), admin_trans('admin.required', ['attribute' => admin_trans('admin.password')]));

        $this->passwordHandler($data);

        $columns = $this->getTableColumns();

        $model = $this->getModel();

        return $this->saveData($data, $columns, $model);
    }

    public function update($primaryKey, $data): bool
    {
        $this->checkUsernameUnique($data['username'], $primaryKey);
        $this->passwordHandler($data);

        $columns = $this->getTableColumns();

        $model = $this->query()->whereKey($primaryKey)->first();

        return $this->saveData($data, $columns, $model);
    }

    public function checkUsernameUnique($username, $id = 0): void
    {
        $exists = $this->query()
            ->where('username', $username)
            ->when($id, fn($query) => $query->where('id', '<>', $id))
            ->exists();

        admin_abort_if($exists, admin_trans('admin.admin_user.username_already_exists'));
    }

    public function updateUserSetting($primaryKey, $data): bool
    {
        $this->passwordHandler($data, $primaryKey);

        return parent::update($primaryKey, $data);
    }

    public function passwordHandler(&$data, $id = null): void
    {
        $password = Arr::get($data, 'password');

        if ($password) {
            admin_abort_if($password !== Arr::get($data, 'confirm_password'), admin_trans('admin.admin_user.password_confirmation'));

            if ($id) {
                admin_abort_if(!Arr::get($data, 'old_password'), admin_trans('admin.admin_user.old_password_required'));

                $oldPassword = $this->query()->where('id', $id)->value('password');

                admin_abort_if(!Hash::check($data['old_password'], $oldPassword), admin_trans('admin.admin_user.old_password_error'));
            }

            $data['password'] = password_hash($password,PASSWORD_DEFAULT);;

            unset($data['confirm_password']);
            unset($data['old_password']);
        }
    }

    public function list(): array
    {
        $keyword = request()->input('keyword');

        $query = $this->query()
            ->with('roles')
            ->select(['id', 'name', 'username', 'avatar', 'enabled', 'created_at'])
            ->when($keyword, function ($query) use ($keyword) {
                $query->where('username', 'like', "%{$keyword}%")->orWhere('name', 'like', "%{$keyword}%");
            });

        $this->sortable($query);

        $list = $query->paginate(request()->input('perPage', 20));
        $items = $list->items();
        $total = $list->total();

        return compact('items', 'total');
    }

    /**
     * @param           $data
     * @param array     $columns
     * @param AdminUser $model
     *
     * @return bool
     */
    protected function saveData($data, array $columns, AdminUser $model): bool
    {
        $roles = Arr::pull($data, 'roles');

        foreach ($data as $k => $v) {
            if (!in_array($k, $columns)) {
                continue;
            }

            $model->setAttribute($k, $v);
        }

        if ($model->save()) {
            $model->roles()->sync(Arr::has($roles, '0.id') ? Arr::pluck($roles, 'id') : $roles);

            return true;
        }

        return false;
    }

    public function delete(string $ids): bool
    {
        $exists = $this->query()
            ->whereIn($this->primaryKey(), explode(',', $ids))
            ->whereHas('roles', fn($q) => $q->where('slug', 'administrator'))
            ->exists();

        admin_abort_if($exists, admin_trans('admin.admin_user.cannot_delete'));

        return parent::delete($ids);
    }
}
