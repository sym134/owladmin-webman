<?php

namespace plugin\owladmin\app\service\system;

use plugin\owladmin\app\service\AdminService;
use plugin\owladmin\app\model\system\Attachment;

class AttachmentService extends AdminService
{
    public function __construct()
    {
        parent::__construct();
        $this->modelName = Attachment::class;
    }
}
