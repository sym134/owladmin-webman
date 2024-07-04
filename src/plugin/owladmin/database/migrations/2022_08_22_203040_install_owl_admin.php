<?php

use Illuminate\Database\Schema\Blueprint;
use Eloquent\Migrations\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $this->schema()->create('admin_users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 120)->unique();
            $table->string('password', 80);
            $table->tinyInteger('enabled')->default(1);
            $table->string('name')->default('');
            $table->string('avatar')->nullable();
            $table->string('remember_token', 100)->nullable();
            $table->timestamps();
        });

        $this->schema()->create('admin_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->string('slug', 50)->unique();
            $table->timestamps();
        });

        $this->schema()->create('admin_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->string('slug', 50)->unique();
            $table->text('http_method')->nullable();
            $table->text('http_path')->nullable();
            $table->integer('order')->default(0);
            $table->integer('parent_id')->default(0);
            $table->timestamps();
        });

        $this->schema()->create('admin_menus', function (Blueprint $table) {
            $table->id();
            $table->integer('parent_id')->default(0);
            $table->integer('order')->default(0);
            $table->string('title', 100)->comment('菜单名称');
            $table->string('icon', 100)->nullable()->comment('菜单图标');
            $table->string('url')->nullable()->comment('菜单路由');
            $table->tinyInteger('url_type')->default(1)->comment('路由类型(1:路由,2:外链,3:iframe)');
            $table->tinyInteger('visible')->default(1)->comment('是否可见');
            $table->tinyInteger('is_home')->default(0)->comment('是否为首页');
            $table->tinyInteger('keep_alive')->nullable()->comment('页面缓存');
            $table->string('iframe_url')->nullable()->comment('iframe_url');
            $table->string('component')->nullable()->comment('菜单组件');
            $table->tinyInteger('is_full')->default(0)->comment('是否是完整页面');
            $table->string('extension')->nullable()->comment('扩展');

            $table->timestamps();
        });

        $this->schema()->create('admin_role_users', function (Blueprint $table) {
            $table->integer('role_id');
            $table->integer('user_id');
            $table->index(['role_id', 'user_id']);
            $table->timestamps();
        });

        $this->schema()->create('admin_role_permissions', function (Blueprint $table) {
            $table->integer('role_id');
            $table->integer('permission_id');
            $table->index(['role_id', 'permission_id']);
            $table->timestamps();
        });

        $this->schema()->create('admin_permission_menu', function (Blueprint $table) {
            $table->integer('permission_id');
            $table->integer('menu_id');
            $table->index(['permission_id', 'menu_id']);
            $table->timestamps();
        });

        $this->schema()->create('admin_code_generators', function (Blueprint $table) {
            $table->id();
            $table->string('title')->default('')->comment('名称');
            $table->string('table_name')->default('')->comment('表名');
            $table->string('primary_key')->default('id')->comment('主键名');
            $table->string('model_name')->default('')->comment('模型名');
            $table->string('controller_name')->default('')->comment('控制器名');
            $table->string('service_name')->default('')->comment('服务名');
            $table->longText('columns')->comment('字段信息');
            $table->tinyInteger('need_timestamps')->default(0)->comment('是否需要时间戳');
            $table->tinyInteger('soft_delete')->default(0)->comment('是否需要软删除');
            $table->text('needs')->nullable()->comment('需要生成的代码');
            $table->text('menu_info')->nullable()->comment('菜单信息');
            $table->text('page_info')->nullable()->comment('页面信息');
            $table->text('save_path')->nullable()->comment('保存位置');
            $table->timestamps();
        });

        $this->schema()->create('admin_settings', function (Blueprint $table) {
            $table->string('key')->default('');
            $table->longText('values')->nullable();
            $table->timestamps();
        });

        $this->schema()->create('admin_extensions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->tinyInteger('is_enabled')->default(0);
            $table->timestamps();
        });

        $this->schema()->create('admin_pages', function (Blueprint $table) {
            $table->id();
            $table->string('title')->comment('页面名称');
            $table->string('sign')->comment('页面标识');
            $table->longText('schema')->comment('页面结构');
            $table->timestamps();
        });

        $this->schema()->create('admin_relationships', function (Blueprint $table) {
            $table->id();
            $table->string('model')->comment('模型');
            $table->string('title')->comment('关联名称');
            $table->string('type')->comment('关联类型');
            $table->string('remark')->comment('关联名称')->nullable();
            $table->text('args')->comment('关联参数')->nullable();
            $table->text('extra')->comment('额外参数')->nullable();
            $table->timestamps();
        });

        $this->schema()->create('admin_apis', function (Blueprint $table) {
            $table->id();
            $table->string('title')->comment('接口名称');
            $table->string('path')->comment('接口路径');
            $table->string('template')->comment('接口模板');
            $table->tinyInteger('enabled')->default(1)->comment('是否启用');
            $table->longText('args')->comment('接口参数')->nullable();
            $table->timestamps();
        });

        $this->schema()->create('attachments', function (Blueprint $table) {
            $table->comment('附件管理');
            $table->increments('id');
            $table->enum('storage_mode', ['local', 'qiniu', 'aliyun', 'qcloud'])->comment('存储模式');
            $table->string('origin_name')->nullable()->comment('原文件名');
            $table->string('new_name')->nullable()->comment('新文件名');
            $table->string('hash')->nullable()->comment('文件hash');
            $table->enum('file_type', ['image', 'video', 'audio', 'file'])->comment('资源类型');
            $table->string('mime_type')->comment('资源类型');
            $table->string('storage_path')->nullable()->comment('存储目录');
            $table->bigInteger('size_byte')->comment('字节数');
            $table->string('file_size')->nullable()->comment('文件大小');
            $table->string('url')->nullable()->comment('url地址');
            $table->string('remark')->nullable()->comment('备注');
            $table->tinyInteger('created_by')->comment('创建者');
            $table->timestamps();
            $table->softDeletes();
        });

        $this->schema()->create('admin_operation_log', function (Blueprint $table) {
            $table->comment('操作日志');
            $table->increments('id');
            $table->string('username', 20)->nullable()->comment('用户名');
            $table->string('app', 50)->nullable()->comment('应用名称');
            $table->string('method')->nullable()->comment('请求方式');
            $table->string('router')->nullable()->comment('请求路由');
            $table->string('service_name')->nullable()->comment('业务名称');
            $table->string('ip', 45)->nullable()->comment('请求IP地址');
            $table->string('ip_location')->nullable()->comment('IP所属地');
            $table->text('request_data')->nullable()->comment('请求数据');
            $table->string('remark')->nullable()->comment('备注');
            $table->bigInteger('created_by')->index()->comment('创建者');
            $table->dateTime('created_at')->nullable();
            $table->softDeletes();
        });

        $this->schema()->create('admin_login_log', function (Blueprint $table) {
            $table->comment('登录日志');
            $table->increments('id');
            $table->string('username')->nullable()->comment('用户名');
            $table->string('ip')->nullable()->comment('登录IP地址');
            $table->string('ip_location')->nullable()->comment('IP所属地');
            $table->string('os', 50)->nullable()->comment('操作系统');
            $table->string('browser', 50)->nullable()->comment('浏览器');
            $table->unsignedSmallInteger('status')->default(new \Illuminate\Database\Query\Expression('1'))->comment('登录状态');
            $table->string('message', 50)->nullable()->comment('提示消息');
            $table->dateTime('login_time')->nullable()->comment('登录时间');
            $table->string('remark')->nullable()->comment('备注');
            $table->dateTime('created_at')->nullable();
            $table->softDeletes();
        });

        $this->schema()->create('admin_crontab', function (Blueprint $table) {
            $table->comment('定时任务');
            $table->increments('id');
            $table->string('name')->nullable()->comment('任务名称');
            $table->unsignedSmallInteger('task_type')->comment('任务类型');
            $table->enum('execution_cycle', ['day', 'hour', 'week', 'month', 'second-n', 'day-n', 'hour-n', 'minute-n'])->comment('执行周期');
            $table->string('target', 500)->nullable()->comment('调用目标');
            $table->string('parameter', 1000)->nullable()->comment('任务参数');
            $table->string('rule', 32)->nullable()->comment('表达式');
            $table->unsignedTinyInteger('week')->default(1)->comment('周');
            $table->unsignedTinyInteger('day')->default(1)->comment('天');
            $table->unsignedTinyInteger('hour')->default(0)->comment('小时');
            $table->unsignedTinyInteger('minute')->default(0)->comment('分钟');
            $table->unsignedTinyInteger('second')->default(0)->comment('秒');
            $table->unsignedTinyInteger('task_status')->default(0)->comment('状态');
            $table->string('remark')->nullable()->comment('备注');
            $table->unsignedInteger('created_by')->comment('创建者');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['name', 'deleted_at']);
        });

        $this->schema()->create('admin_crontab_log', function (Blueprint $table) {
            $table->comment('定时任务日志');
            $table->increments('id');
            $table->unsignedInteger('crontab_id')->index()->comment('任务ID');
            $table->string('target', 500)->comment('调用目标');
            $table->string('parameter', 1000)->comment('调用参数');
            $table->string('exception_info', 2000)->nullable()->comment('异常信息');
            $table->unsignedTinyInteger('execution_status')->default(0)->comment('执行状态');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        $this->schema()->dropIfExists('admin_users');
        $this->schema()->dropIfExists('admin_roles');
        $this->schema()->dropIfExists('admin_permissions');
        $this->schema()->dropIfExists('admin_menus');
        $this->schema()->dropIfExists('admin_role_users');
        $this->schema()->dropIfExists('admin_role_permissions');
        $this->schema()->dropIfExists('admin_permission_menu');
        $this->schema()->dropIfExists('admin_code_generators');
        $this->schema()->dropIfExists('admin_settings');
        $this->schema()->dropIfExists('admin_extensions');
        $this->schema()->dropIfExists('admin_pages');
        $this->schema()->dropIfExists('admin_relationships');
        $this->schema()->dropIfExists('admin_apis');
        $this->schema()->dropIfExists('attachments');
        $this->schema()->dropIfExists('admin_operation_log');
        $this->schema()->dropIfExists('admin_login_log');
        $this->schema()->dropIfExists('admin_crontab');
        $this->schema()->dropIfExists('admin_crontab_log');
    }
};
