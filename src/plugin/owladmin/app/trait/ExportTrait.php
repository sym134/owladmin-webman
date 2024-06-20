<?php

namespace plugin\owladmin\app\trait;

use support\Response;

trait ExportTrait
{
    /**
     * 导出
     *
     * @return Response
     */
    protected function export(): Response
    {
        admin_abort_if(!class_exists('\Rap2hpoutre\FastExcel\FastExcel'), admin_trans('admin.export.please_install_laravel_excel'));

        // 默认在 storage/app/ 下
        $path = 'resource/' . sprintf('%s-%s.xlsx', $this->exportFileName(), date('YmdHis'));

        // 导出本页和导出选中项都是通过 _ids 查询
        $ids = request()->input('_ids');

        // listQuery() 为列表查询条件，与获取列表数据一致
        $query = $this->service->listQuery()
            ->when($ids, fn($query) => $query->whereIn($this->service->primaryKey(), explode(',', $ids)));

        try {
            (new \Rap2hpoutre\FastExcel\FastExcel($query->get()->toArray()))->export(base_path($path), fn($row) => $this->exportMap($row));
        } catch (\Throwable $e) {
            admin_abort(admin_trans('admin.action_failed'));
        }

        return $this->response()->success(compact('path'));
    }

    /**
     * @param $row
     *
     * @return mixed
     */
    protected function exportMap($row): mixed
    {
        return $row;
    }

    /**
     * 导出文件名
     *
     * @return string
     */
    protected function exportFileName(): string
    {
        return strtolower(str_replace('Controller', '', class_basename($this)));
    }
}
