<?php

use Webman\Route;
use plugin\jzadmin\middleware\Authenticate;
use plugin\jzadmin\controller\AuthController;
use plugin\jzadmin\controller\IndexController;
use plugin\jzadmin\controller\AdminRoleController;
use plugin\jzadmin\controller\AdminUserController;
use plugin\jzadmin\controller\AdminMenuController;
use plugin\jzadmin\controller\DevTools\EditorController;
use plugin\jzadmin\controller\AdminPermissionController;
use plugin\jzadmin\controller\DevTools\ExtensionController;
use plugin\jzadmin\controller\DevTools\CodeGeneratorController;

Route::get('/admin', function () {
    return view('admin-assets/index');
});

// require_once app_path('admin/config/Route.php'); // 取消注释
//
// config('plugin.saiadmin.saithink.captcha.mode', 'session');
Route::group('/' . config('plugin.jzadmin.jzadmin.route.prefix'), function () {
    Route::get('/login', [AuthController::class, 'loginPage']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/logout', [AuthController::class, 'logout']);
    Route::get('/captcha', [AuthController::class, 'reloadCaptcha']);
    Route::get('/current-user', [AuthController::class, 'currentUser']);

    Route::get('/menus', [IndexController::class, 'menus']);
    Route::get('/_settings', [IndexController::class, 'settings']);
    Route::post('/_settings', [IndexController::class, 'saveSettings']);
    Route::get('/no-content', [IndexController::class, 'noContentResponse']);
    Route::get('/_download_export', [IndexController::class, 'downloadExport']);

    Route::any('/upload_file', [IndexController::class, 'uploadFile']);
    Route::any('/upload_rich', [IndexController::class, 'uploadRich']);
    Route::any('/upload_image', [IndexController::class, 'uploadImage']);
    Route::get('/user_setting', [AuthController::class, 'userSetting']);
    Route::put('user_setting', [AuthController::class, 'saveUserSetting']);


    Route::group('/system', function () {
        Route::get('/', [AdminUserController::class, 'index']);

        Route::resource('/admin_users', AdminUserController::class);
        Route::resource('/admin_menus', AdminMenuController::class);
        Route::resource('/admin_roles', AdminRoleController::class);
        Route::resource('/admin_permissions', AdminPermissionController::class);

        Route::post('/admin_role_save_permissions', [AdminRoleController::class, 'savePermissions']);
        Route::post('/_admin_permissions_auto_generate', [AdminPermissionController::class, 'autoGenerate']);
    });

    if (config('plugin.jzadmin.jzadmin.show_development_tools')) {
        Route::group('/dev_tools', function () {
            Route::resource('/code_generator', CodeGeneratorController::class);
            Route::group('/code_generator', function () {
                Route::post('/preview', [CodeGeneratorController::class, 'preview']);
                Route::post('/generate', [CodeGeneratorController::class, 'generate']);
                Route::post('/get_record', [CodeGeneratorController::class, 'getRecord']);
                Route::post('/get_property_options', [CodeGeneratorController::class, 'getPropertyOptions']);

                Route::group('/component_property', function () {
                    Route::post('/', [CodeGeneratorController::class, 'saveComponentProperty']);
                    Route::post('/list', [CodeGeneratorController::class, 'getComponentProperty']);
                    Route::post('/del', [CodeGeneratorController::class, 'delComponentProperty']);
                });
            });

            Route::resource('/extensions', ExtensionController::class);
            Route::group('/extensions', function () {
                Route::post('/more', [ExtensionController::class, 'more']);
                Route::post('/enable', [ExtensionController::class, 'enable']);
                Route::post('/install', [ExtensionController::class, 'install']);
                Route::post('/uninstall', [ExtensionController::class, 'uninstall']);
                Route::post('/get_config', [ExtensionController::class, 'getConfig']);
                Route::post('/save_config', [ExtensionController::class, 'saveConfig']);
                Route::post('/config_form', [ExtensionController::class, 'configForm']);
            });

            Route::post('/editor_parse', [EditorController::class, 'index']);
        });
    }

    // Route::resource('/systemConfig', SystemConfigController::class);          // 站点设置
    // Route::resource('/systemSettingAttachment', AttachmentController::class); // 附件管理
    // Route::resource('/systemSettingFiles', SettingFilesController::class);
    // Route::resource('/systemSettingPay', SettingPayController::class);
    // Route::resource('/systemSettingSmsConfig', SettingSmsConfigController::class);
    // Route::resource('/config', ConfigController::class);
    //
    //
    // // 微信公众号
    // Route::group('/wechat',function (){
    //     Route::post('/uploadVerificationFile', [\Jizhi\JzAdmin\controller\admin\wechat\ConfigController::class, 'uploadVerificationFile']);
    //     Route::resource('/wechatConfig', \Jizhi\JzAdmin\controller\admin\wechat\ConfigController::class);
    //     Route::resource('/wechatFans', WechatFanController::class);
    //     Route::resource('/wechatMenu', WechatMenuController::class);
    //     Route::resource('/wechatTemplate', WechatTemplateController::class);
    //     // 小程序
    //     Route::resource('/miniProgramConfig', \Jizhi\JzAdmin\controller\admin\miniProgram\ConfigController::class);
    // });
    //
    // //短信
    // Route::group('/sms', function () {
    //     Route::resource('/smsTemplate', SmsTemplateController::class);
    //     Route::resource('/smsGroup', SmsGroupRecordController::class);
    //     Route::post('/sendSms', [SmsGroupRecordController::class, 'sendSms']);
    //     Route::resource('/smsRecord', SmsRecordController::class);
    // });


})->middleware([
    Authenticate::class,
    // Permission::class
]);
