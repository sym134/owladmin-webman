<?php

namespace plugin\owladmin\app\service\system;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\Eloquent\Builder;
use plugin\owladmin\app\service\AdminService;
use plugin\owladmin\app\model\system\AdminCrontab;

/**
 * 定时任务
 *
 * @method AdminCrontab getModel()
 * @method AdminCrontab|\Illuminate\Database\Query\Builder query()
 */
class AdminCrontabService extends AdminService
{
    protected string $modelName = AdminCrontab::class;

    public function store($data): bool
    {
        $data['rule'] = $this->generateCrontabExpression($data['execution_cycle'], $data['second'], $data['minute'], $data['hour'], $data['day'], '*', $data['week']);
        $data['created_by'] = $this->request->user->id;
        $this->validateTask($data['task_type'], $data['target']);
        return parent::store($data);
    }

    public function update($primaryKey, $data): bool
    {
        $data['rule'] = $this->generateCrontabExpression($data['execution_cycle'], $data['second'], $data['minute'], $data['hour'], $data['day'], '*', $data['week']);
        $data['created_by'] = $this->request->user->id;
        $this->validateTask($data['task_type'], $data['target']);
        return parent::update($primaryKey, $data);
    }

    public function listQuery(): Builder
    {
        return parent::listQuery();
    }

    /**
     * 验证任务
     *
     * @param string $task_type
     * @param string $target
     *
     * @return void
     * @throws \Exception Author:sym
     * Date:2024/7/2 下午3:28
     * Company:极智科技
     */
    private function validateTask(string $task_type, string $target): void
    {
        if ((int)$task_type === 3) {
            if (!str_contains($target, ':')) {
                throw new \Exception('类任务格式错误');
            }
            [$class, $fun] = explode(':', $target);
            if (!class_exists($class)) {
                throw new \Exception('类任务不存在:' . $class);
            }
            if (!method_exists($class, $fun)) {
                throw new \Exception('类任务:' . $class . ',方法:' . $fun . ',未找到');
            }
        }
    }

    /**
     * 生成Crontab表达式
     * @param $executionPeriod
     * @param $second
     * @param $minute
     * @param $hour
     * @param $dayOfMonth
     * @param $month
     * @param $dayOfWeek
     *
     * @return string
     *
     * Author:sym
     * Date:2024/7/4 下午6:34
     * Company:极智科技
     */
    private function generateCrontabExpression($executionPeriod, $second = '*', $minute = '*', $hour = '*', $dayOfMonth = '*', $month = '*', $dayOfWeek = '*'): string
    {
        // 设置默认值
        $second = ($second !== null && $second !== '') ? $second : '*';
        $minute = ($minute !== null && $minute !== '') ? $minute : '*';
        $hour = ($hour !== null && $hour !== '') ? $hour : '*';
        $dayOfMonth = ($dayOfMonth !== null && $dayOfMonth !== '') ? $dayOfMonth : '*';
        $month = ($month !== null && $month !== '') ? $month : '*';
        $dayOfWeek = ($dayOfWeek !== null && $dayOfWeek !== '') ? $dayOfWeek : '*';

        switch ($executionPeriod) {
            case 'day':
                // 每天执行
                $minute = ($minute !== '*') ? $minute : '0';
                $hour = ($hour !== '*') ? $hour : '0';
                $dayOfMonth = '*';
                $month = '*';
                $dayOfWeek = '*';
                break;
            case 'day-n':
                // 每 N 天执行
                $minute = ($minute !== '*') ? $minute : '0';
                $hour = ($hour !== '*') ? $hour : '0';
                $dayOfMonth = "*/$dayOfMonth";
                $month = '*';
                $dayOfWeek = '*';
                break;
            case 'hour':
                // 每小时执行
                $minute = ($minute !== '*') ? $minute : '0';
                $hour = '*';
                $dayOfMonth = '*';
                $month = '*';
                $dayOfWeek = '*';
                break;
            case 'hour-n':
                // 每 N 小时执行
                $minute = ($minute !== '*') ? $minute : '0';
                $hour = "*/$hour";
                $dayOfMonth = '*';
                $month = '*';
                $dayOfWeek = '*';
                break;
            case 'minute-n':
                // 每 N 分钟执行
                $minute = "*/$minute";
                $hour = '*';
                $dayOfMonth = '*';
                $month = '*';
                $dayOfWeek = '*';
                break;
            case 'week':
                // 每周执行
                $minute = ($minute !== '*') ? $minute : '0';
                $hour = ($hour !== '*') ? $hour : '0';
                $dayOfMonth = '*';
                $month = '*';
                $dayOfWeek = ($dayOfWeek !== '*') ? $dayOfWeek : '0';
                break;
            case 'month':
                // 每月执行
                $minute = ($minute !== '*') ? $minute : '0';
                $hour = ($hour !== '*') ? $hour : '0';
                $dayOfMonth = ($dayOfMonth !== '*') ? $dayOfMonth : '1';
                $month = '*';
                $dayOfWeek = '*';
                break;
            case 'second-n':
                $second = ($second !== '*') ? '*/' . $second : '0';
                $minute = "*";
                $hour = '*';
                $dayOfMonth = '*';
                $month = '*';
                $dayOfWeek = '*';
                break;
            default:
                return "Invalid execution period.";
        }

        // 组合成 crontab 表达式
        return "$second $minute $hour $dayOfMonth $month $dayOfWeek";
    }

    /**
     * crontab表达式到文本
     * @param string $executionPeriod
     * @param string $expression
     *
     * @return string
     *
     * Author:sym
     * Date:2024/7/4 下午6:34
     * Company:极智科技
     */
    public function crontabExpressionToText(string $executionPeriod,string $expression): string
    {
        $parts = explode(' ', $expression);

        if (count($parts) != 6) {
            return "Invalid crontab expression.";
        }

        [$second, $minute, $hour, $dayOfMonth, $month, $dayOfWeek] = $parts;

        // 定义一个用于返回的文本数组
        $text = [];

        // 处理不同的执行周期
        switch ($executionPeriod) {
            case 'second-n':
                return $this->convertPeriod($second, '秒', 'second-n');
            case 'minute-n':
                return $this->convertPeriod($minute, '分钟', 'minute-n');
            case 'hour-n':
                return $this->convertPeriod($hour, '小时', 'hour-n');
            case 'day-n':
                return $this->convertPeriod($dayOfMonth, '天', 'day-n');
            case 'day':
                $text[] = "每天";
                break;
            case 'hour':
                $text[] = "每小时";
                break;
            case 'week':
                $text[] = "每周";
                break;
            case 'month':
                $text[] = "每月";
                break;
            default:
                return "Invalid execution period.";
        }

        // 处理周几
        if ($dayOfWeek !== '*') {
            $days = ['日', '一', '二', '三', '四', '五', '六'];
            $text[] = "周" . $days[$dayOfWeek];
        }

        // 处理每月的哪一天
        if ($dayOfMonth !== '*' && $executionPeriod !== 'day-n') {
            $text[] = "每月" . $dayOfMonth . "日";
        }

        // 处理月份
        if ($month !== '*') {
            $text[] = $month . "月";
        }

        // 处理小时和分钟
        if ($hour !== '*') {
            $text[] = sprintf("%02d", $hour) . "时";
        }

        if ($minute !== '*') {
            $text[] = sprintf("%02d", $minute) . "分";
        }

        // 处理秒
        if ($second !== '*' && $executionPeriod !== 'second-n') {
            $text[] .= sprintf("%02d", $second) . "秒";
        }

        // 生成最终的文本描述
        $finalText = implode(' ', array_filter($text));

        // 优化输出
        if ($executionPeriod == 'hour' && strpos($finalText, "00时") !== false) {
            $finalText = str_replace("00时", "", $finalText);
            $finalText = "每小时第 " . trim($finalText) . " 执行一次";
        } else {
            $finalText = $finalText . " 执行一次";
        }

        return $finalText;
    }

    /**
     * 辅助函数，用于处理 'n' 周期
     * @param $part
     * @param $unit
     * @param $periodType
     *
     * @return string
     *
     * Author:sym
     * Date:2024/7/4 下午6:33
     * Company:极智科技
     */
    private function convertPeriod($part, $unit, $periodType): string
    {
        if (preg_match('/^\*\/(\d+)$/', $part, $matches)) {
            return "每隔 " . $matches[1] . " " . $unit . "执行一次";
        } else {
            return "Invalid expression for " . $periodType . ".";
        }
    }

        /**
     * 运行任务
     *
     * @param int $id
     *
     * @return bool
     *
     * Author:sym
     * Date:2024/7/2 下午3:29
     * Company:极智科技
     */
    public function run(int $id): bool
    {
        $info = $this->getModel()->find($id);
        $data['crontab_id'] = $info->id;
        $data['name'] = $info->name;
        $data['target'] = $info->target;
        $data['parameter'] = $info->parameter;
        switch ($info->task_type) {
            case 1:
                // URL任务GET
                $httpClient = new Client([
                    'timeout' => 5,
                    'verify'  => false,
                ]);
                try {
                    $res = $httpClient->request('GET', $info->target, [
                        'form_params' => $info->parameter,
                    ]);
                    $data['execution_status'] = $res->getStatusCode() === 200 ? 1 : 2;
                    // $data['exception_info'] = $res->getBody()->getContents();
                    AdminCrontabLogService::make()->store($data);
                    return true;
                } catch (GuzzleException $e) {
                    $data['execution_status'] = 2;
                    $data['exception_info'] = $e->getMessage();
                    AdminCrontabLogService::make()->store($data);
                    return false;
                }
            case 2:
                // URL任务POST
                $httpClient = new Client([
                    'timeout' => 5,
                    'verify'  => false,
                ]);
                try {
                    $res = $httpClient->request('POST', $info->target, [
                        'form_params' => $info->parameter,
                    ]);
                    $data['execution_status'] = $res->getStatusCode() === 200 ? 1 : 2;
                    // $data['exception_info'] = $res->getBody()->getContents();
                    AdminCrontabLogService::make()->store($data);
                    return true;
                } catch (GuzzleException $e) {
                    $data['execution_status'] = 2;
                    $data['exception_info'] = $e->getMessage();
                    AdminCrontabLogService::make()->store($data);
                    return false;
                }
            case 3:
                // 类任务
                [$class_name, $method_name] = explode(':', $info->target);
                $class = new $class_name;
                if (method_exists($class, $method_name)) {
                    $return = $class->$method_name($info->parameter);
                    $data['execution_status'] = 1;
                    $data['exception_info'] = $return;
                    AdminCrontabLogService::make()->store($data);
                    return true;
                } else {
                    $data['execution_status'] = 2;
                    $data['exception_info'] = '类:' . $class_name . ',方法:run,未找到';
                    AdminCrontabLogService::make()->store($data);
                    return false;

                }
            default:
                return false;
        }
    }
}
