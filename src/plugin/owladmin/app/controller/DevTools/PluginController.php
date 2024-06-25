<?php

namespace plugin\owladmin\app\controller\DevTools;

use support\Request;
use support\Response;
use plugin\owladmin\app\renderer\CRUDTable;
use plugin\owladmin\app\plugin\PluginService;
use plugin\owladmin\app\renderer\DialogAction;
use plugin\owladmin\app\controller\AdminController;

class PluginController extends AdminController
{
    protected string $serviceName = PluginService::class;

    /**
     * @return Response
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function index(): Response
    {
        if ($this->actionOfGetData()) {
            $data = $this->service->list();
            foreach ($data['items'] as $key => $extension) {
                $data['items'][$key] = $this->each($extension);
            }

            return $this->response()->success($data);
        }

        $page = $this->basePage()->body($this->list());

        return $this->response()->success($page);
    }

    protected function each($extension)
    {
        $property = $this->service->configApp($extension->name);
        return [
            'id'          => $extension->id,
            // 'alias'       => $extension->getAlias(),
            // 'logo'        => $extension->getLogoBase64(),
            'name'        => $extension->name,
            'version'     => $property['version'] ?? '',
            'description' => $property['description'] ?? '',
            'authors'     => $property['authors'] ?? '未知',
            'homepage'    => $property['homepage'] ?? '',
            'enabled'     => $extension['is_enabled'],
            // 'extension'   => $extension,
            // 'doc'         => $extension->getDocs(),
            // 'has_setting' => $extension->settingForm() instanceof Form,
            // 'used'        => $extension->used(),
        ];
    }

    public function list(): CRUDTable
    {
        return amis()->CRUDTable()
            ->perPage(20)
            ->affixHeader(false)
            ->filterTogglable()
            ->filterDefaultVisible(false)
            ->api($this->getListGetDataPath())
            ->perPageAvailable([10, 20, 30, 50, 100, 200])
            ->footerToolbar(['switch-per-page', 'statistics', 'pagination'])
            ->loadDataOnce()
            ->source('${rows | filter:alias:match:keywords}')
            ->filter(
                $this->baseFilter()->body([
                    amis()->TextControl()
                        ->name('keywords')
                        ->label(admin_trans('admin.extensions.form.name'))
                        ->placeholder(admin_trans('admin.extensions.filter_placeholder'))
                        ->size('md'),
                ])
            )
            ->headerToolbar([
                $this->createExtend(),
                // $this->localInstall(),
                // $this->moreExtend(),
                amis('reload')->align('right'),
                amis('filter-toggler')->align('right'),
            ])
            ->columns([
                amis()->TableColumn('alias', admin_trans('admin.extensions.form.name'))
                    ->type('tpl')
                    ->tpl('
<div class="flex">
    <div> <img src="${logo}" class="w-10 mr-4"/> </div>
    <div>
        <div><a href="${homepage}" target="_blank">${alias | truncate:30}</a></div>
        <div class="text-gray-400">${name}</div>
    </div>
</div>
'),
                amis()->TableColumn('author', admin_trans('admin.extensions.card.author'))
                    ->type('tpl')
                    ->tpl('<div>${authors.name}</div> <span class="text-gray-400">${authors.email}</span>'),
                $this->rowActions([
                    amis()->DrawerAction()->label(admin_trans('admin.show'))->className('p-0')->level('link')->drawer(
                        amis()->Drawer()
                            ->size('lg')
                            ->title('README.md')
                            ->actions([])
                            ->closeOnOutside()
                            ->closeOnEsc()
                            ->body(amis()->Markdown()->name('${doc | raw}')->options([
                                'html'   => true,
                                'breaks' => true,
                            ]))
                    ),
                    amis()->DrawerAction()
                        ->label(admin_trans('admin.extensions.setting'))
                        ->level('link')
                        ->visibleOn('${has_setting && enabled}')
                        ->drawer(
                            amis()
                                ->Drawer()
                                ->title(admin_trans('admin.extensions.setting'))
                                ->resizable()
                                ->closeOnOutside()
                                ->body(
                                    amis()->Service()
                                        ->schemaApi([
                                            'url'    => admin_url('dev_tools/extensions/config_form'),
                                            'method' => 'post',
                                            'data'   => [
                                                'id' => '${id}',
                                            ],
                                        ])
                                )
                                ->actions([])
                        ),
                    amis()->AjaxAction()
                        ->label('${enabled ? "' . admin_trans('admin.extensions.disable') . '" : "' . admin_trans('admin.extensions.enable') . '"}')
                        ->level('link')
                        ->className(["text-success" => '${!enabled}', "text-danger" => '${enabled}'])
                        ->api([
                            'url'    => admin_url('dev_tools/plugin/enable'),
                            'method' => 'post',
                            'data'   => [
                                'id'      => '${id}',
                                'enabled' => '${!enabled}',
                            ],
                        ])
                        ->confirmText('${enabled ? "' . admin_trans('admin.extensions.disable_confirm') . '" : "' . admin_trans('admin.extensions.enable_confirm') . '"}'),
                    amis()->AjaxAction()
                        ->label(admin_trans('admin.extensions.uninstall'))
                        ->level('link')
                        ->className('text-danger')
                        ->api([
                            'url'    => admin_url('dev_tools/extensions/uninstall'),
                            'method' => 'post',
                            'data'   => ['id' => '${id}'],
                        ])
                        ->visibleOn('${used}')
                        ->confirmText(admin_trans('admin.extensions.uninstall_confirm')),
                ]),
            ]);
    }

    /**
     * 创建扩展
     *
     * @return DialogAction
     */
    public function createExtend(): DialogAction
    {
        return amis()->DialogAction()
            ->label(admin_trans('admin.extensions.create_extension'))
            ->icon('fa fa-add')
            ->level('success')
            ->dialog(
                amis()->Dialog()->title(admin_trans('admin.extensions.create_extension'))->body(
                    amis()->Form()->mode('normal')->api($this->getStorePath())->body([
                        amis()->Alert()
                            ->level('info')
                            ->showIcon()
                            ->body(admin_trans('admin.extensions.create_tips', ['dir' => config('plugin.owladmin.admin.extension.dir')])),
                        amis()->TextControl()
                            ->name('name')
                            ->label(admin_trans('admin.extensions.form.name'))
                            ->placeholder('foo')
                            ->required(),
                    ])
                )
            );
    }

    public function enable(Request $request)
    {
        $response = fn($result) => $this->autoResponse($result, admin_trans('admin.save'));
        return $response($this->service->enable($request->all()) > 0);
    }

    /**
     * 新增
     *
     * @param Request $request
     *
     * @return Response
     *
     * Author:sym
     * Date:2024/6/18 上午10:47
     * Company:极智科技
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

        if ($this->service->store($request->all())) {
            return $this->response()->successMessage(admin_trans('admin.save') . admin_trans('admin.successfully'));
        }

        return $this->response()->fail($this->service->getError() ?? admin_trans('admin.save') . admin_trans('admin.failed'));
    }
}
