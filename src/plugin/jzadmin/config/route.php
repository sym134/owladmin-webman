<?php

use Webman\Route;
use plugin\jzadmin\app\Admin;
use plugin\jzadmin\app\middleware\Permission;
use plugin\jzadmin\app\middleware\Authenticate;
use plugin\jzadmin\app\controller\AuthController;
use plugin\jzadmin\app\controller\HomeController;
use plugin\jzadmin\app\controller\IndexController;
use plugin\jzadmin\app\controller\AdminRoleController;
use plugin\jzadmin\app\controller\AdminUserController;
use plugin\jzadmin\app\controller\AdminMenuController;
use plugin\jzadmin\app\controller\DevTools\ApiController;
use plugin\jzadmin\app\controller\DevTools\PagesController;
use plugin\jzadmin\app\controller\DevTools\EditorController;
use plugin\jzadmin\app\controller\AdminPermissionController;
use plugin\jzadmin\app\controller\DevTools\ExtensionController;
use plugin\jzadmin\app\controller\DevTools\RelationshipController;
use plugin\jzadmin\app\controller\DevTools\CodeGeneratorController;

Route::get('/admin1',[plugin\jzadmin\app\controller\IndexController::class,'index']);

Route::get('/admin', fn() => Admin::view());


Route::group('/' . config('plugin.jzadmin.admin.route.prefix'), function () {

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
   Route::get('/_iconify_search', [IndexController::class, 'iconifySearch']);
   Route::get('/page_schema', [IndexController::class, 'pageSchema']);

   Route::any('upload_file', [IndexController::class, 'uploadFile']);
   Route::any('upload_rich', [IndexController::class, 'uploadRich']);
   Route::any('upload_image', [IndexController::class, 'uploadImage']);
   Route::get('/user_setting', [AuthController::class, 'userSetting']);
   Route::put('user_setting', [AuthController::class, 'saveUserSetting']);

   Route::resource('/dashboard', HomeController::class);

   Route::group('/system', function () {
       Route::get('/', [AdminUserController::class, 'index']);

       Route::resource('/admin_users', AdminUserController::class);
       Route::post('/admin_menus/save_order', [AdminMenuController::class, 'saveOrder']);
       Route::resource('/admin_menus', AdminMenuController::class);
       Route::resource('/admin_roles', AdminRoleController::class);
       Route::resource('/admin_permissions', AdminPermissionController::class);

       Route::post('/admin_role_save_permissions', [AdminRoleController::class, 'savePermissions']);
       Route::post('/_admin_permissions_auto_generate', [AdminPermissionController::class, 'autoGenerate']);
    });

    if (config('plugin.jzadmin.admin.show_development_tools')) {
       Route::group('/dev_tools', function () {
           Route::resource('/code_generator', CodeGeneratorController::class);
           Route::group('/code_generator', function () {
               Route::post('/preview', [CodeGeneratorController::class, 'preview']);
               Route::post('/generate', [CodeGeneratorController::class, 'generate']);
               Route::post('/clear', [CodeGeneratorController::class, 'clear']);
               Route::post('/gen_record_options', [CodeGeneratorController::class, 'genRecordOptions']);
               Route::post('/form_data', [CodeGeneratorController::class, 'formData']);
               Route::post('/get_record', [CodeGeneratorController::class, 'getRecord']);
               Route::post('/get_property_options', [CodeGeneratorController::class, 'getPropertyOptions']);

               Route::group('/component_property', function () {
                   Route::post('/', [CodeGeneratorController::class, 'saveComponentProperty']);
                   Route::post('/list', [CodeGeneratorController::class, 'getComponentProperty']);
                   Route::post('/del', [CodeGeneratorController::class, 'delComponentProperty']);
                });

               Route::group('/common_field', function (){
                   Route::post('/', [CodeGeneratorController::class, 'saveColumnProperty']);
                   Route::post('/list', [CodeGeneratorController::class, 'getColumnProperty']);
                   Route::post('/del', [CodeGeneratorController::class, 'delColumnProperty']);
                });
            });

           Route::resource('/extensions', ExtensionController::class);
           Route::group('/extensions', function () {
               Route::post('/enable', [ExtensionController::class, 'enable']);
               Route::post('/install', [ExtensionController::class, 'install']);
               Route::post('/uninstall', [ExtensionController::class, 'uninstall']);
               Route::post('/get_config', [ExtensionController::class, 'getConfig']);
               Route::post('/save_config', [ExtensionController::class, 'saveConfig']);
               Route::post('/config_form', [ExtensionController::class, 'configForm']);
            });

           Route::post('/editor_parse', [EditorController::class, 'index']);

           Route::resource('/pages', PagesController::class);

           Route::resource('/relationships', RelationshipController::class);
           Route::group('/relation', function () {
               Route::get('/model_options', [RelationshipController::class, 'modelOptions']);
               Route::get('/column_options', [RelationshipController::class, 'columnOptions']);
               Route::get('/all_models', [RelationshipController::class, 'allModels']);
               Route::post('/generate_model', [RelationshipController::class, 'generateModel']);
            });

           Route::resource('/apis', ApiController::class);
           Route::group('/api', function () {
               Route::get('/templates', [ApiController::class, 'template']);
               Route::get('/args_schema', [ApiController::class, 'argsSchema']);
               Route::post('/add_template', [ApiController::class, 'addTemplate']);
            });
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
    //     Route::resource('/uploadVerificationFile', [\Jizhi\JzAdmin\controller\admin\wechat\ConfigController::class, 'uploadVerificationFile']);
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
    //     Route::resource('/sendSms', [SmsGroupRecordController::class, 'sendSms']);
    //     Route::resource('/smsRecord', SmsRecordController::class);
    // });


})->middleware([
    // Authenticate::class,
    // Permission::class
]);
