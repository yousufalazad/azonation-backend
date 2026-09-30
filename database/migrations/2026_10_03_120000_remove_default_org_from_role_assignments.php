<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * model_has_roles / model_has_permissions.org_type_user_id had DEFAULT 1 in the database
 * (added by hand, not by a migration). A role saved without an organisation would then
 * silently apply to organisation 1. Without the default, such a save fails instead.
 * The code always sets the organisation, so nothing that works today is affected.
 */
return new class extends Migration
{
    private const TABLES = ['model_has_roles', 'model_has_permissions'];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            DB::statement("ALTER TABLE `$table` ALTER COLUMN `org_type_user_id` DROP DEFAULT");
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $table) {
            DB::statement("ALTER TABLE `$table` ALTER COLUMN `org_type_user_id` SET DEFAULT 1");
        }
    }
};
