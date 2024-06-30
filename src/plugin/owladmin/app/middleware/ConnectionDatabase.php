<?php

namespace plugin\owladmin\app\middleware;

use Webman\Http\Request;
use Webman\Http\Response;
use Webman\MiddlewareInterface;
use plugin\saas\app\model\TenantModel;
use plugin\owladmin\app\support\SqlRecord;

/**
 * 连接数据库
 * ConnectionDatabase
 * plugin\saas\app\middleware
 *
 * Author:sym
 * Date:2024/6/17 下午3:47
 * Company:极智网络科技
 */
class ConnectionDatabase implements MiddlewareInterface
{
    public function process(Request $request, callable $handler): Response
    {
        if (config('app.debug')){
            SqlRecord::$sql = []; // 清空sql记录
        }
        // $request->header('x-site-domain')
        // $domain = $request->host(true)?? 'https://newtrain.tinywan.com';
        // $platform = TenantModel::where('domain', $domain)->field('id, domain, website')->findOrEmpty();
        // if (!$platform->isEmpty()) {
        //     $request->tenant = $platform['website'];
        // }
        return $handler($request);
    }

}
