<?php

namespace plugin\owladmin\app\exception;

use support\Response;
use plugin\owladmin\app\Admin;

class AdminException extends \Exception
{
    private mixed $data;
    private mixed $doNotDisplayToast;

    public function __construct($message = "", $data = [], $doNotDisplayToast = 0)
    {
        parent::__construct($message);

        $this->data              = $data;
        $this->doNotDisplayToast = $doNotDisplayToast;
    }

    public function render(): Response
    {
        return Admin::response()->doNotDisplayToast($this->doNotDisplayToast)->fail($this->getMessage(), $this->data);
    }
}
