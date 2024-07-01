<?php

namespace plugin\owladmin\app\controller;

use support\Request;
use support\Response;
use yzh52521\hash\Hash;
use Webman\Event\Event;
use plugin\owladmin\app\Admin;
use Illuminate\Http\JsonResponse;
use plugin\owladmin\app\renderer\Page;
use plugin\owladmin\app\support\Captcha;
use plugin\owladmin\app\service\AdminUserService;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthController extends AdminController
{
    protected string $serviceName = AdminUserService::class;

    public function login(Request $request)
    {
        if (Admin::config('admin.auth.login_captcha')) {
            if (!$request->post('captcha')) {
                return $this->response()->fail(admin_trans('admin.required', ['attribute' => admin_trans('admin.captcha')]));
            }
            if (strtolower(cache()->pull($request->post('sys_captcha'))) != strtolower($request->post('captcha'))) { // webman $request->post
                return $this->response()->fail(admin_trans('admin.captcha_error'));
            }
        }

        try {
            $validator = validate([
                'username' => 'require',
                'password' => 'require',
            ], [
                'username.require' => admin_trans('admin.required', ['attribute' => admin_trans('admin.username')]),
                'password.require' => admin_trans('admin.required', ['attribute' => admin_trans('admin.password')]),
            ]);
            if (!$validator->check($request->all())) {
                abort(400, $validator->getError());
            }

            $user = Admin::adminUserModel()::query()->where('username', $request->post('username'))->first();

            if ($user && Hash::check($request->post('password'), $user->password)) {
                if (!$user->enabled) {
                    // 登录事件
                    Event::emit('user.login', ['username' => $user->name, 'status' => 3, 'message' => '用户未启用']);
                    return $this->response()->fail(admin_trans('admin.user_disabled'));
                }

                // $module = Admin::currentModule(true); // webman
                // $prefix = $module ? $module . '.' : ''; // webman
                $token = $this->guard()->login($user)->access_token;

                // 登录事件
                Event::emit('user.login', ['username' => $user->name, 'status' => 1, 'message' => '登陆成功']);
                return $this->response()->success(compact('token'), admin_trans('admin.login_successful'));
            }

            // 登录事件
            Event::emit('user.login', ['username' => $user->name, 'status' => 2, 'message' => '登陆失败']);
            abort(400, admin_trans('admin.login_failed'));
        } catch (\Exception $e) {
            return $this->response()->fail($e->getMessage());
        }
    }

    public function loginPage(): Page
    {
        $form = amis()->Form()
            ->panelClassName('border-none')
            ->id('login-form')
            ->title()
            ->api(admin_url('/login'))
            ->initApi('/no-content')
            ->body([
                amis()->TextControl()->name('username')->placeholder(admin_trans('admin.username'))->required(),
                amis()
                    ->TextControl()
                    ->type('input-password')
                    ->name('password')
                    ->placeholder(admin_trans('admin.password'))
                    ->required(),
                amis()->InputGroupControl('captcha_group')->body([
                    amis()->TextControl('captcha', admin_trans('admin.captcha'))->placeholder(admin_trans('admin.captcha'))->required(),
                    amis()->HiddenControl()->name('sys_captcha'),
                    amis()->Service()->id('captcha-service')->api('get:' . admin_url('/captcha'))->body(
                        amis()->Image()
                            ->src('${captcha_img}')
                            ->height('1.917rem')
                            ->className('p-0 captcha-box')
                            ->imageClassName('rounded-r')
                            ->set(
                                'clickAction',
                                ['actionType' => 'reload', 'target' => 'captcha-service']
                            )
                    ),
                ])->visibleOn('${!!login_captcha}'),
                amis()->CheckboxControl()->name('remember_me')->option(admin_trans('admin.remember_me'))->value(true),

                // 登录按钮
                amis()->VanillaAction()
                    ->actionType('submit')
                    ->label(admin_trans('admin.login'))
                    ->level('primary')
                    ->className('w-full'),
            ])
            // 清空默认的提交按钮
            ->actions([])
            ->onEvent([
                // 页面初始化事件
                'inited'     => [
                    'actions' => [
                        // 读取本地存储的登录参数
                        [
                            'actionType' => 'custom',
                            'script'     => <<<JS
let loginParams = localStorage.getItem(window.\$owl.getCacheKey('loginParams'))
if(loginParams){
    loginParams = JSON.parse(decodeURIComponent(window.atob(loginParams)))
    doAction({
        actionType: 'setValue',
        componentId: 'login-form',
        args: { value: loginParams }
    })
}
JS
                            ,

                        ],
                    ],
                ],
                // 登录成功事件
                'submitSucc' => [
                    'actions' => [
                        // 保存登录参数到本地, 并跳转到首页
                        [
                            'actionType' => 'custom',
                            'script'     => <<<JS
let _data = {}
if(event.data.remember_me){
    _data = { username: event.data.username, password: event.data.password }
}
window.\$owl.afterLoginSuccess(_data, event.data.result.data.token)
JS,

                        ],
                    ],
                ],

                // 登录失败事件
                'submitFail' => [
                    'actions' => [
                        // 刷新验证码外层Service
                        ['actionType' => 'reload', 'componentId' => 'captcha-service'],
                    ],
                ],
            ]);

        $card = amis()->Card()->className('w-96 m:w-full')->body([
            amis()->Service()->api('/_settings')->body([
                amis()->Flex()->justify('space-between')->className('px-2.5 pb-2.5')->items([
                    amis()->Image()->src('${logo}')->width(40)->height(40),
                    amis()->Tpl()
                        ->className('font-medium')
                        ->tpl('<div style="font-size: 24px">${app_name}</div>'),
                ]),
                $form,
            ]),
        ]);

        return amis()->Page()->className('login-bg')->css([
            '.captcha-box .cxd-Image--thumb' => [
                'padding' => '0',
                'cursor'  => 'pointer',
                'border'  => 'var(--Form-input-borderWidth) solid var(--Form-input-borderColor)',

                'border-top-right-radius'    => '4px',
                'border-bottom-right-radius' => '4px',
            ],
            '.cxd-Image-thumb'               => ['width' => 'auto'],
            '.login-bg'                      => [
                'background' => 'var(--owl-body-bg)',
            ],
        ])->body(
            amis()->Wrapper()->className("h-screen w-full flex items-center justify-center")->body($card)
        );
    }

    /**
     * 刷新验证码
     *
     * @return Response
     */
    public function reloadCaptcha(): Response
    {
        $captcha = new Captcha();

        $captcha_img = $captcha->showImg();
        $sys_captcha = uniqid('captcha-');

        cache()->put($sys_captcha, $captcha->getCaptcha(), 600);

        return $this->response()->success(compact('captcha_img', 'sys_captcha'));
    }

    public function logout(): Response
    {
        $this->guard()->user()->currentAccessToken()->delete();

        return $this->response()->successMessage();
    }

    protected function guard()
    {
        return Admin::guard();
    }

    public function currentUser(): Response
    {
        if (!Admin::config('admin.auth.enable')) {
            return $this->response()->success([]);
        }

        $userInfo = Admin::user()->only(['name', 'avatar']);

        $menus = amis()->DropdownButton()
            ->hideCaret()
            ->trigger('hover')
            ->label($userInfo['name'])
            ->className('h-full w-full')
            ->btnClassName('navbar-user w-full')
            ->menuClassName('min-w-0')
            ->set('icon', $userInfo['avatar'])
            ->buttons([
                amis()->VanillaAction()
                    ->iconClassName('pr-2')
                    ->icon('fa fa-user-gear')
                    ->label(admin_trans('admin.user_setting'))
                    ->onClick('window.location.hash = "#/user_setting"'),
                amis()->VanillaAction()
                    ->iconClassName('pr-2')
                    ->label(admin_trans('admin.logout'))
                    ->icon('fa-solid fa-right-from-bracket')
                    ->onClick('window.$owl.logout()'),
            ]);

        return $this->response()->success(array_merge($userInfo, compact('menus')));
    }

    public function userSetting(): Response
    {
        $form = amis()->Form()
            ->title()
            ->panelClassName('px-48 m:px-0')
            ->mode('horizontal')
            ->initApi('/current-user')
            ->api('put:' . admin_url('/user_setting'))
            ->body([
                amis()->ImageControl()
                    ->label(admin_trans('admin.admin_user.avatar'))
                    ->name('avatar')
                    ->receiver($this->uploadImagePath()),
                amis()->TextControl()->label(admin_trans('admin.admin_user.name'))->name('name')->required(),
                amis()->TextControl()->type('input-password')->label(admin_trans('admin.old_password'))->name('old_password'),
                amis()->TextControl()->type('input-password')->label(admin_trans('admin.password'))->name('password'),
                amis()->TextControl()
                    ->type('input-password')
                    ->label(admin_trans('admin.confirm_password'))
                    ->name('confirm_password'),
            ]);

        return $this->response()->success(amis()->Page()->body($form));
    }

    public function saveUserSetting(): Response
    {
        $result = $this->service->updateUserSetting($this->user()->id,
            request()->only([
                'avatar',
                'name',
                'old_password',
                'password',
                'confirm_password',
            ]));

        return $this->autoResponse($result);
    }
}
