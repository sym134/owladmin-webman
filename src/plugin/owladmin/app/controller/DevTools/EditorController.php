<?php

namespace plugin\owladmin\app\controller\DevTools;

use support\Response;
use Illuminate\Support\Arr;
use plugin\owladmin\app\renderer\RendererMap;
use plugin\owladmin\app\controller\AdminController;

class EditorController extends AdminController
{
    public function index():Response
    {
        $schema = $this->parse(request()->input('schema'));

        return $this->response()->success(compact('schema'));
    }

    public function parse($json): string
    {
        $code    = '';
        $map     = RendererMap::$map;
        $mapKeys = array_keys($map);

        if ($json['type'] ?? null) {
            if (in_array($json['type'], $mapKeys)) {
                $className = str_replace('plugin\\owladmin\\app\\renderer\\', '', $map[$json['type']]);
                $code      .= sprintf('amis()->%s()', $className);
            } else {
                // 没找到对应的组件
                $code .= sprintf('amis(\'%s\')', $json['type']);
            }

            foreach ($json as $key => $value) {
                if ($key == 'type') {
                    continue;
                }
                // 属性
                if (is_array($value)) {
                    $code .= sprintf('->%s(%s)', $key, $this->parse($value));
                } else {
                    $code .= sprintf('->%s(\'%s\')', $key, $this->escape($value));
                }
            }
        } else {
            // json 转 php 数组
            $code = '[';
            foreach ($json as $key => $value) {
                if (is_array($value)) {
                    if (Arr::isList($json)) {
                        $code .= sprintf('%s,', $this->parse($value));
                    } else {
                        $code .= sprintf('\'%s\' => %s,', $key, $this->parse($value));
                    }
                } else {
                    $code .= sprintf('\'%s\' => \'%s\',', $key, $this->escape($value));
                }
            }
            $code .= ']';
        }

        return $code;
    }

    /**
     * 转义单引号
     *
     * @param $string
     *
     * @return string|string[]
     */
    public function escape($string): array|string
    {
        return str_replace("'", "\'", $string);
    }
}
