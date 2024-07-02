<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $tableNames = config('permission.table_names');

        if (empty($tableNames)) {
            throw new Exception('Error: config/permission.php not loaded. Run [php artisan config:clear] and try again.');
        }

        // Update the model_type from 'App\User' to 'App\Models\User'
        DB::table($tableNames['model_has_permissions'])
            ->where('model_type', 'App\User')
            ->update(['model_type' => 'App\Models\User']);
        DB::table($tableNames['model_has_roles'])
            ->where('model_type', 'App\User')
            ->update(['model_type' => 'App\Models\User']);

        app('cache')
            ->store(config('permission.cache.store') != 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');

        if (empty($tableNames)) {
            throw new Exception('Error: config/permission.php not loaded. Run [php artisan config:clear] and try again.');
        }

        // Update the model_type from 'App\Models\User' to 'App\User'
        DB::table($tableNames['model_has_permissions'])
            ->where('model_type', 'App\Models\User')
            ->update(['model_type' => 'App\User']);
        DB::table($tableNames['model_has_roles'])
            ->where('model_type', 'App\Models\User')
            ->update(['model_type' => 'App\User']);

        app('cache')
            ->store(config('permission.cache.store') != 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }
};
