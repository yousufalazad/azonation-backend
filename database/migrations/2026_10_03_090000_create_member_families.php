<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Family members belong to the member (not an organisation). The member decides,
 * per organisation, whether it sees nothing, just the numbers, or the details.
 * Events can say "families welcome"; the organisation then sees an estimated headcount.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_family_people', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->comment('The member (individual) this family belongs to');
            $table->string('name', 100);
            $table->enum('relationship', ['spouse', 'child', 'parent', 'sibling', 'grandparent', 'grandchild', 'other']);
            $table->unsignedSmallInteger('birth_year')->nullable()->comment('Year only: enough for age groups');
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('note', 255)->nullable()->comment('e.g. dietary or access needs');
            $table->timestamps();
            $table->index('user_id');
        });

        Schema::create('member_family_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->comment('The member sharing');
            $table->foreignId('org_id')->constrained('users')->cascadeOnDelete()->comment('The organisation it is shared with');
            $table->enum('level', ['numbers', 'details'])->default('numbers');
            $table->timestamps();
            $table->unique(['user_id', 'org_id']);
            $table->index('org_id');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->boolean('family_welcome')->default(false)->after('conduct_type');
        });

        // Who in an organisation may see its members' shared family information
        $now = now();
        $permissionId = DB::table('permissions')->where('name', 'member-family.read')->value('id')
            ?? DB::table('permissions')->insertGetId(['name' => 'member-family.read', 'guard_name' => 'web', 'created_at' => $now, 'updated_at' => $now]);

        // The organisation account itself gets it through its "admin" role and its plan role
        $roleIds = DB::table('roles')
            ->whereIn('name', DB::table('management_packages')->pluck('slug')->push('admin'))
            ->pluck('id');
        foreach ($roleIds as $roleId) {
            DB::table('role_has_permissions')->insertOrIgnore(['permission_id' => $permissionId, 'role_id' => $roleId]);
        }
    }

    public function down(): void
    {
        $permissionId = DB::table('permissions')->where('name', 'member-family.read')->value('id');
        if ($permissionId) {
            DB::table('role_has_permissions')->where('permission_id', $permissionId)->delete();
            DB::table('permissions')->where('id', $permissionId)->delete();
        }
        Schema::table('events', fn (Blueprint $table) => $table->dropColumn('family_welcome'));
        Schema::dropIfExists('member_family_shares');
        Schema::dropIfExists('member_family_people');
    }
};
