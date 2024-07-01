<?php

namespace plugin\owladmin\app\service\monitor;

use plugin\owladmin\app\service\AdminService;
use plugin\owladmin\app\model\monitor\AdminLoginLog;

/**
 * 登录日志
 *
 * @method AdminLoginLog getModel()
 * @method AdminLoginLog|\Illuminate\Database\Query\Builder query()
 */
class AdminLoginLogService extends AdminService
{
	protected string $modelName = AdminLoginLog::class;
}
