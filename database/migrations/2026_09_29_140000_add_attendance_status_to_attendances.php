<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Attendance now has two parts:
 *  - type (how they took part): In Person, Online, Phone, ...
 *  - status (what happened): Present, Late, Absent, Excused, ...
 * Someone who did not attend has a status but no type, so type becomes optional.
 */
return new class extends Migration
{
    // Statuses that mean the person did not take part
    private const NOT_ATTENDED = ['Absent', 'Excused', 'No Show', 'Not marked'];

    public function up(): void
    {
        Schema::table('attendance_statuses', function (Blueprint $table) {
            $table->boolean('is_attended')->default(true)->after('name')
                ->comment('1 = the person took part (Present, Late...), 0 = did not (Absent, Excused...)');
            $table->unsignedSmallInteger('sort_order')->default(0)->after('is_attended');
        });
        DB::table('attendance_statuses')->whereIn('name', self::NOT_ATTENDED)->update(['is_attended' => false]);
        DB::table('attendance_statuses')->update(['sort_order' => DB::raw('id')]);
        // "Not marked" means there is no record yet; it is worked out, never chosen
        DB::table('attendance_statuses')->where('name', 'Not marked')->update(['is_active' => false]);

        foreach (['meeting_attendances', 'meeting_guest_attendances'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->unsignedBigInteger('attendance_type_id')->nullable()->change();
                $table->foreignId('attendance_status_id')->nullable()->after('attendance_type_id')
                    ->constrained('attendance_statuses')->nullOnDelete();
            });
        }

        // Everyone already marked with a type was there: mark them Present
        $present = DB::table('attendance_statuses')->where('name', 'Present')->value('id');
        if ($present) {
            foreach (['meeting_attendances', 'meeting_guest_attendances'] as $tableName) {
                DB::table($tableName)->whereNotNull('attendance_type_id')->whereNull('attendance_status_id')
                    ->update(['attendance_status_id' => $present]);
            }
        }
    }

    public function down(): void
    {
        foreach (['meeting_attendances', 'meeting_guest_attendances'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropConstrainedForeignId('attendance_status_id');
            });
        }
        Schema::table('attendance_statuses', function (Blueprint $table) {
            $table->dropColumn(['is_attended', 'sort_order']);
        });
    }
};
