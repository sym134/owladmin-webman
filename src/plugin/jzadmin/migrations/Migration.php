<?php

namespace plugin\jzadmin\migrations;

use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Builder;

abstract class Migration extends \Illuminate\Database\Migrations\Migration
{
    public Connection $db;

    protected function db(): Connection
    {
        return $this->db;
    }

    public function schema(): Builder
    {
        return $this->db()->getSchemaBuilder();
    }

    abstract public function up();

    abstract public function down();
}
