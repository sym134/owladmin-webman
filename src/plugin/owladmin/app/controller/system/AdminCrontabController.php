<?php

namespace plugin\owladmin\app\controller\system;

use support\Response;
use plugin\owladmin\app\renderer\Form;
use plugin\owladmin\app\renderer\Page;
use plugin\owladmin\app\model\system\AdminCrontab;
use plugin\owladmin\app\controller\AdminController;
use plugin\owladmin\app\service\system\AdminCrontabService;

/**
 * 定时任务
 *
 * @property AdminCrontabService $service
 */
class AdminCrontabController extends AdminController
{
    protected string $serviceName = AdminCrontabService::class;

    public function list(): Page
    {
        $crud = $this->baseCRUD()
            ->filterTogglable(false)
            ->headerToolbar([
                $this->createButton(),
                ...$this->baseHeaderToolBar(),
            ])
            ->columns([
                amis()->TableColumn('id', 'ID')->sortable(),
                amis()->TableColumn('name', admin_trans('crontab.name')),
                amis()->TableColumn('created_by', admin_trans('crontab.created_by')),
                amis()->TableColumn('task_type', admin_trans('crontab.task_type'))->type('mapping')->map(AdminCrontab::TASK_TYPE),
                amis()->TableColumn('execution_cycle_text', admin_trans('crontab.execution_cycle')),
                amis()->SwitchControl('task_status', admin_trans('crontab.task_status'))->trueValue(1)->falseValue(2)->required()->value(1),
                amis()->TableColumn('created_at', admin_trans('admin.created_at'))->sortable(),
                $this->rowActions([
                    amis()->VanillaAction()->id('u:a53d1837f6be')->label(admin_trans('crontab.run'))->icon('fa-solid fa-play')->level('link')
                        ->onEvent(['click' => [
                            'actions' => [[
                                              'ignoreError' => '', 'outputVar' => 'responseResult', 'actionType' => 'ajax', 'options' => [],
                                              'api'         => ['url' => admin_url('/system/admin_crontab_run'), 'method' => 'get', 'data' => ['id' => '${id}',],],
                                          ],],
                        ],])
                        ->confirmText('确认立即执行'),
                    amis()->DrawerAction()->drawer(
                        amis()->Drawer()->title(admin_trans('crontab.execution_log'))->body((new AdminCrontabLogController)->list(admin_url('/system/admin_crontab_log?_action=getData&crontab_id=${id}')))->size('xl')->resizable()
                    )->label(admin_trans('crontab.execution_log'))->icon('fa-solid fa-clock-rotate-left')->level('link'),
                    $this->rowEditButton(true, 'lg'),
                    $this->rowDeleteButton(),
                ]),
            ]);

        return $this->baseList($crud);
    }

    public function form($isEdit = false): Form
    {
        return $this->baseForm()->mode('horizontal')->data([
            'week'   => 1,
            'day'    => 1,
            'hour'   => 1,
            'minute' => 30,
            'second' => 1,
        ])->body([
            amis()->SelectControl('task_type', admin_trans('crontab.task_type'))->options(AdminCrontab::TASK_TYPE)->value(1)
                ->required()
                ->onEvent([
                    'change' => [
                        'actions' => [
                            [
                                'actionType'  => 'setValue',
                                'componentId' => 'name',
                                'args'        => ['value' => '${event.data.selectedItems.label}'],
                            ],
                        ],
                    ],
                ]),
            amis()->TextControl('name', admin_trans('crontab.name'))->id('name')->required()->value(AdminCrontab::TASK_TYPE[1])
                ->description(admin_trans('crontab.name_description')),
            amis()->GroupControl()->label(admin_trans('crontab.execution_cycle'))->body([
                amis()->SelectControl('execution_cycle')->mode('inline')->options([
                    'day'      => admin_trans('crontab.execution_cycle_options.day'),
                    'day-n'    => admin_trans('crontab.execution_cycle_options.day-n'),
                    'hour'     => admin_trans('crontab.execution_cycle_options.hour'),
                    'hour-n'   => admin_trans('crontab.execution_cycle_options.hour-n'),
                    'minute-n' => admin_trans('crontab.execution_cycle_options.minute-n'),
                    'week'     => admin_trans('crontab.execution_cycle_options.week'),
                    'month'    => admin_trans('crontab.execution_cycle_options.month'),
                    'second-n' => admin_trans('crontab.execution_cycle_options.second-n'),
                ])->value('day'),

                amis()->SelectControl('week')->mode('inline')->options([
                    0 => '星期日',
                    1 => '星期一',
                    2 => '星期二',
                    3 => '星期三',
                    4 => '星期四',
                    5 => '星期五',
                    6 => '星期六',
                ])->value(1)->visibleOn('execution_cycle===\'week\''),
                amis()->InputGroupControl()->mode('inline')->visibleOn('execution_cycle===\'day-n\'||execution_cycle===\'month\'')->body([
                    amis()->NumberControl('day')->mode('inline')->value(1)->min(1)->max(31),
                    amis()->Button()->level('secondary')->label(admin_trans('crontab.day')),
                ]),
                amis()->InputGroupControl()->mode('inline')->visibleOn('execution_cycle!==\'hour\'&&execution_cycle!==\'minute-n\'&&execution_cycle!==\'second-n\'')->body([
                    amis()->NumberControl('hour')->value(1)->min(0)->max(23),
                    amis()->Button()->level('secondary')->label(admin_trans('crontab.hour')),
                ]),
                amis()->InputGroupControl()->mode('inline')->visibleOn('execution_cycle!==\'second-n\'')->body([
                    amis()->NumberControl('minute')->value(30)->min(0)->max(59),
                    amis()->Button()->level('secondary')->label(admin_trans('crontab.minute')),
                ]),
                amis()->InputGroupControl()->mode('inline')->visibleOn('execution_cycle==\'second-n\'')->body([
                    amis()->NumberControl('second')->value(1)->min(1)->max(59),
                    amis()->Button()->level('secondary')->label(admin_trans('crontab.second')),
                ]),

            ]),
            amis()->TextControl('target', admin_trans('crontab.target'))->required()->description(admin_trans('crontab.target_description')),
            amis()->BaseRenderer()->set('type', 'json-schema')->set('name', 'parameter')->set('label', admin_trans('crontab.parameter')),
            amis()->SwitchControl('task_status', admin_trans('crontab.task_status'))->trueValue(1)->falseValue(2)->required()->value(1),
            amis()->TextControl('remark', admin_trans('crontab.remark')),
        ]);
    }

    public function detail(): Form
    {
        return $this->baseDetail()->body([
        ]);
    }

    public function run(): Response
    {
        if ($this->service->run(request()->get('id'))) {
            return $this->response()->successMessage(admin_trans('crontab.run') . admin_trans('admin.successfully'));
        }
        return $this->response()->fail(admin_trans('crontab.run') . admin_trans('admin.failed') . $this->service->getError());
    }
}
