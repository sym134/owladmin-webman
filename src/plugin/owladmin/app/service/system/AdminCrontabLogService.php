<?php

namespace plugin\owladmin\app\service\system;

use plugin\owladmin\app\service\AdminService;
use plugin\owladmin\app\model\system\AdminCrontabLog;

/**
 * 定时任务日志
 *
 * @method AdminCrontabLog getModel()
 * @method AdminCrontabLog|\Illuminate\Database\Query\Builder query()
 */
class AdminCrontabLogService extends AdminService
{
	protected string $modelName = AdminCrontabLog::class;
}
