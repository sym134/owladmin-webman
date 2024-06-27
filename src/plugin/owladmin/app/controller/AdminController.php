<?php

namespace plugin\owladmin\app\controller;

use support\Request;
use support\Response;
use BadMethodCallException;
use plugin\owladmin\app\Admin;
use plugin\owladmin\app\model\AdminUser;
use plugin\owladmin\app\trait\ExportTrait;
use plugin\owladmin\app\trait\UploadTrait;
use plugin\owladmin\app\trait\ElementTrait;
use plugin\owladmin\app\trait\QueryPathTrait;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Container\ContainerExceptionInterface;
use plugin\owladmin\app\trait\CheckActionTrait;
use Illuminate\Contracts\Auth\Authenticatable;
use plugin\owladmin\app\support\Cores\JsonResponse;

abstract class AdminController
{
    use ExportTrait;
    use UploadTrait;
    use ElementTrait;
    use QueryPathTrait;
    use CheckActionTrait;

    protected $service;

    /** @var string $queryPath 路径 */
    protected string $queryPath;

    /** @var string|mixed $adminPrefix 路由前缀 */
    protected string $adminPrefix;

    /** @var bool $isCreate 是否是新增页面, 页面模式时生效 */
    protected bool $isCreate = false;

    /** @var bool $isEdit 是否是编辑页面, 页面模式时生效 */
    protected bool $isEdit = false;

    public function __construct()
    {
        if (property_exists($this, 'serviceName')) {
            $this->service = $this->serviceName::make();
        }

        $this->adminPrefix = Admin::config('admin.route.prefix');

        $this->queryPath = str_replace($this->adminPrefix . '/', '', request()->path());
    }

    /**
     * 获取当前登录用户
     *
     * @return Authenticatable|AdminUser|null
     */
    public function user(): AdminUser|Authenticatable|null
    {
        return Admin::user();
    }

    /**
     * @param $request
     *
     * @return mixed
     */
    public function getPrimaryValue($request): mixed
    {
        $primaryKey = $this->service->primaryKey();

        return $request->input($primaryKey);
    }

    /**
     * 后台响应
     *
     * @return JsonResponse
     */
    protected function response(): JsonResponse
    {
        return Admin::response();
    }

    protected function autoResponse($flag, $text = ''): Response
    {
        if (!$text) {
            $text = admin_trans('admin.actions');
        }

        if ($flag) {
            return $this->response()->successMessage($text . admin_trans('admin.successfully'));
        }

        return $this->response()->fail($this->service->getError() ?? $text . admin_trans('admin.failed'));
    }

    public function index():Response
    {
        if ($this->actionOfGetData()) {
            return $this->response()->success($this->service->list());
        }

        if ($this->actionOfExport()) {
            return $this->export();
        }

        return $this->response()->success($this->list());
    }

    /**
     * 获取新增页面
     *
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function create(): Response
    {
        $this->isCreate = true;

        $form = amis()
            ->Card()
            ->className('base-form')
            ->header(['title' => admin_trans('admin.create')])
            ->toolbar([$this->backButton()])
            ->body($this->form(false)->api($this->getStorePath()));

        $page = $this->basePage()->body($form);

        return $this->response()->success($page);
    }

    /**
     * 新增保存
     *
     * @param Request $request
     *
     * @return Response
     */
    public function store(Request $request): Response
    {
        $response = fn($result) => $this->autoResponse($result, admin_trans('admin.save'));

        if ($this->actionOfQuickEdit()) {
            return $response($this->service->quickEdit($request->all()));
        }

        if ($this->actionOfQuickEditItem()) {
            return $response($this->service->quickEditItem($request->all()));
        }

        return $response($this->service->store($request->all()));
    }

    /**
     * 详情
     *
     * @param $id
     *
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function show($id): Response
    {
        if ($this->actionOfGetData()) {
            return $this->response()->success($this->service->getDetail($id));
        }

        $detail = amis()
            ->Card()
            ->className('base-form')
            ->header(['title' => admin_trans('admin.detail')])
            ->body($this->detail())
            ->toolbar([$this->backButton()]);

        $page = $this->basePage()->body($detail);

        return $this->response()->success($page);
    }

    /**
     * 获取编辑页面
     *
     * @param $id
     *
     * @return Response
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function edit($id): Response
    {
        $this->isEdit = true;

        if ($this->actionOfGetData()) {
            return $this->response()->success($this->service->getEditData($id));
        }

        $form = amis()
            ->Card()
            ->className('base-form')
            ->header(['title' => admin_trans('admin.edit')])
            ->toolbar([$this->backButton()])
            ->body(
                $this->form(true)->api($this->getUpdatePath())->initApi($this->getEditGetDataPath())
            );

        $page = $this->basePage()->body($form);

        return $this->response()->success($page);
    }

    /**
     * 编辑保存
     *
     * @param Request $request
     *
     * @return Response
     */
    public function update(Request $request): Response
    {
        $result = $this->service->update($this->getPrimaryValue($request), $request->all());

        return $this->autoResponse($result, admin_trans('admin.save'));
    }

    /**
     * 删除
     *
     * @param $ids
     *
     * @return Response
     */
    public function destroy($ids): Response
    {
        $rows = $this->service->delete($ids);

        return $this->autoResponse($rows, admin_trans('admin.delete'));
    }

    public function callAction($method, $parameters)
    {
        return $this->{$method}(...array_values($parameters));
    }

    /**
     * Handle calls to missing methods on the controller.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     *
     * @throws \BadMethodCallException
     */
    public function __call(string $method, array $parameters)
    {
        throw new BadMethodCallException(sprintf(
            'Method %s::%s does not exist.', static::class, $method
        ));
    }
}
