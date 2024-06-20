<?php

namespace plugin\owladmin\app\trait;

trait CheckActionTrait
{
    /**
     * 是否为列表数据请求
     *
     * @return bool
     */
    public function actionOfGetData(): bool
    {
        return request()->input('_action') == 'getData';
    }

    /**
     * 是否为导出数据请求
     *
     * @return bool
     */
    public function actionOfExport(): bool
    {
        return request()->input('_action') == 'export';
    }

    /**
     * 是否为快速编辑数据请求
     *
     * @return bool
     */
    public function actionOfQuickEdit(): bool
    {
        return request()->input('_action') == 'quickEdit';
    }

    /**
     * 是否为快速编辑数据请求
     *
     * @return bool
     */
    public function actionOfQuickEditItem(): bool
    {
        return request()->input('_action') == 'quickEditItem';
    }
}
