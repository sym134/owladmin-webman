<?php

use Illuminate\Database\Migrations\Migration;
use plugin\owladmin\app\support\Cores\Database as DatabaseAlias;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        DatabaseAlias::make()->up();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DatabaseAlias::make()->down();
    }
};
