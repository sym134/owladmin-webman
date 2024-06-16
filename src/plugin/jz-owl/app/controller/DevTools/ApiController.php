<?php

namespace plugin\jzadmin\app\controller\DevTools;

use support\Response;
use Illuminate\Support\Str;
use plugin\jzadmin\app\Admin;
use plugin\jzadmin\app\renderer\Form;
use plugin\jzadmin\app\renderer\Page;
use plugin\jzadmin\app\support\Cores\Api;
use plugin\jzadmin\app\renderer\DialogAction;
use plugin\jzadmin\app\service\AdminApiService;
use plugin\jzadmin\app\support\Apis\AdminBaseApi;
use plugin\jzadmin\app\controller\AdminController;

/**
 * @property AdminApiService $service
 */
class ApiController extends AdminController
{
    protected string $serviceName = AdminApiService::class;

    public function list(): Page
    {
        $crud = $this->baseCRUD()
            ->filterTogglable(false)
            ->headerToolbar([
                $this->createButton(true, 'lg'),
                ...$this->baseHeaderToolBar(),
                $this->appTemplateBtn(),
            ])
            ->columns([
                amis()->TableColumn('id', 'ID')->sortable(),
                amis()->TableColumn('title', admin_trans('admin.apis.title'))->searchable(),
                amis()->TableColumn('path', admin_trans('admin.apis.path'))->searchable(),
                amis()->TableColumn('template_title', admin_trans('admin.apis.template')),
                amis()->TableColumn('enabled', admin_trans('admin.apis.enabled'))->quickEdit(
                    amis()->SwitchControl()->mode('inline')->saveImmediately(true)
                ),
                amis()->TableColumn('updated_at', admin_trans('admin.updated_at'))->type('datetime')->sortable(true),
                $this->rowActions([
                    $this->rowEditButton(true, 'lg'),
                    $this->rowDeleteButton(),
                ]),
            ]);

        return $this->baseList($crud);
    }

    public function appTemplateBtn(): DialogAction
    {
        return amis()
            ->DialogAction()
            ->label(admin_trans('admin.apis.add_template'))
            ->level('success')
            ->icon('fa fa-upload')
            ->dialog(
                amis()->Dialog()->title(admin_trans('admin.apis.add_template'))->body([
                    amis()->Form()->mode('normal')->api('/dev_tools/api/add_template')->body([
                        amis()->TextareaControl('template')
                            ->required()
                            ->minRows(10)
                            ->description(admin_trans('admin.apis.add_template_tips'))
                            ->placeholder(admin_trans('admin.apis.paste_template')),
                        amis()->SwitchControl('overlay', admin_trans('admin.apis.overlay'))->value(1),
                    ]),
                ])
            );
    }

    /**
     * 添加模板
     *
     * @return Response
     */
    public function addTemplate(): Response
    {
        $template  = request()->input('template');
        $className = Str::between($template, 'class ', ' extends AdminBaseApi');
        if (!$className) {
            $className = Str::between($template, 'class ', ' extends \plugin\jzadmin\support\Apis\AdminBaseApi');
        }

        admin_abort_if(!$className, admin_trans('admin.apis.template_format_error'));

        $file = Api::path($className . '.php');

        admin_abort_if(is_file($file) && !request()->input('overlay'), admin_trans('admin.apis.template_exists'));

        try {
            appw('files')->put($file, $template);
        } catch (\Throwable $e) {
            return $this->response()->fail(admin_trans('admin.save_failed'));
        }

        return $this->response()->successMessage(admin_trans('admin.save_success'));
    }

    public function form(): Form
    {
        return $this->baseForm()->body([
            amis()->TextControl('title', admin_trans('admin.apis.title'))->required(),
            amis()->TextControl('path', admin_trans('admin.apis.path'))->required(),
            amis()->SwitchControl('enabled', admin_trans('admin.apis.enabled'))->value(1),
            amis()->SelectControl('template', admin_trans('admin.apis.template'))
                ->required()
                ->searchable()
                ->source('/dev_tools/api/templates'),
            amis()->ComboControl('args', admin_trans('admin.apis.args'))
                ->visibleOn('${template}')
                ->multiLine()
                ->strictMode(false)
                ->items([
                    amis()->Service()->initFetch()->schemaApi('get:/dev_tools/api/args_schema?template=${template}'),
                ]),
        ]);
    }


    public function detail($id): Form
    {
        return $this->baseDetail()->body([]);
    }

    public function template(): Response
    {
        $apis = collect(\support\Container::instance('jzadmin')->get('apis'))
            ->filter(fn($item) => (new \ReflectionClass($item))->isSubclassOf(AdminBaseApi::class))
            ->map(fn($item) => [
                'label' => appw($item)->getMethod() . ' - ' . appw($item)->getTitle(),
                'value' => $item,
            ]);

        return $this->response()->success($apis);
    }

    public function argsSchema(): Response
    {
        $schema = appw(request()->input('template'))->argsSchema();

        if (blank($schema)) {
            $schema = null;
        }

        return $this->response()->success($schema);
    }
}
