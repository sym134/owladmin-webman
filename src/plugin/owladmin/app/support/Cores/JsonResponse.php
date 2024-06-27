<?php

namespace plugin\owladmin\app\support\Cores;

use support\Response;
use plugin\owladmin\app\support\SqlRecord;

class JsonResponse
{
    /** @var array 额外参数 */
    private array $additionalData = [
        'status'            => 0,
        'msg'               => '',
        'doNotDisplayToast' => 0,
    ];

    /**
     * @param string $message
     * @param null   $data
     *
     * @return  Response
     */
    public function fail(string $message = 'Service error', $data = null): Response
    {
        $this->setFailMsg($message);

        return $this->json($data);
    }

    /**
     * @param null   $data
     * @param string $message
     *
     * @return Response
     */
    public function success($data = null, string $message = ''): Response
    {
        $this->setSuccessMsg($message);

        // if ($data instanceof JsonResource) {
        //     return $data->additional($this->additionalData)->response();
        // }

        if ($data === null) {
            $data = (object)$data;
        }

        return $this->json($data);
    }

    private function json($data): Response
    {
        if (config('app.debug')) {
            $this->additionalData['_debug'] = [
                'sql' => SqlRecord::$sql,
            ];
        }
        return json(array_merge($this->additionalData, ['data' => $data])); // webman json()
    }

    /**
     * @param string $message
     *
     * @return Response
     */
    public function successMessage(string $message = ''): Response
    {
        return $this->success([], $message);
    }

    private function setSuccessMsg($message): void
    {
        $this->additionalData['msg'] = $message;
    }

    private function setFailMsg($message): void
    {
        $this->additionalData['msg']    = $message;
        $this->additionalData['status'] = 1;
    }

    /**
     * 配置弹框时间 (ms)
     *
     * @param $timeout
     *
     * @return $this
     */
    public function setMsgTimeout($timeout): static
    {
        return $this->additional(['msgTimeout' => $timeout]);
    }

    /**
     * 添加额外参数
     *
     * @param array $params
     *
     * @return $this
     */
    public function additional(array $params = []): static
    {
        $this->additionalData = array_merge($this->additionalData, $params);

        return $this;
    }

    /**
     * 不显示弹框
     *
     * @return $this
     */
    public function doNotDisplayToast($value = 1): static
    {
        $this->additionalData['doNotDisplayToast'] = $value;

        return $this;
    }
}
