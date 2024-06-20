<?php

namespace plugin\owladmin\app\plugin;

use plugin\owladmin\app\model\Plugin;
use Illuminate\Database\Eloquent\Collection;
use plugin\owladmin\app\service\AdminService;

class PluginService extends AdminService
{
    protected string $modelName = Plugin::class;

    protected array $property = [];
    protected string $path = '';

    // 创建插件
    public function store($data): bool
    {
        if (strtolower($data['name']) === 'app') {
            $this->setError('禁止使用app目录');
            return false;
        }
        // 判断数据库是否存在
        if (!is_null($this->modelName::query()->where('name', $data['name'])->first())) {
            $this->setError('插件已存在');
            return false;
        }
        [$state, $msg] = runCommand('cms-plugin:create ' . $data['name']);
        if ($state) {
            return parent::store($data);
        }
        $this->setError($msg);
        return false;
    }

    /**
     * 获取扩展包路径.
     *
     * @param string|null $path
     *
     * @return string
     * @throws \Exception
     */
    public function path(string $path = null): string
    {
        if (!$this->path) {
            $this->path = config('plugin.owladmin.admin.extension.dir');
            if (!is_dir($this->path)) {
                throw new \Exception("The {$this->path} is not a directory.");
            }
        }

        $path = ltrim($path, '/');

        return $path ? $this->path . '/' . $path : $this->path;
    }

    public function sortable($query): void
    {
        $query->orderByDesc('id');
    }

    public function enable($data): int
    {
        return $this->modelName::query()->where('id', $data['id'])->update(['is_enabled' => $data['enabled'] === 1 ? 0 : 1]);
    }

    /**
     * 插件配置文件
     *
     * @param string $name
     *
     * @return array|null
     *
     * Author:sym
     * Date:2024/6/18 上午11:07
     * Company:极智科技
     */
    public function configApp(string $name): array|null
    {
        return config('plugin.' . $name . '.app');
    }

    public function getPlugins(): array|Collection
    {
        return $this->modelName::query()->where('is_enabled', 1)->get();
    }
}
