<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Event attendance gets the same two parts as meeting attendance:
 * type (how: In Person, Online...) and status (Present, Late, Absent...).
 * Type becomes optional because someone absent has none.
 */
return new class extends Migration
{
    private const TABLES = ['event_attendances', 'event_guest_attendances'];

    public function up(): void
    {
        foreach (self::TABLES as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->unsignedBigInteger('attendance_type_id')->nullable()->change();
                $table->foreignId('attendance_status_id')->nullable()->after('attendance_type_id')
                    ->constrained('attendance_statuses')->nullOnDelete();
            });
        }
        // Everyone already marked with a type was there: mark them Present
        $present = DB::table('attendance_statuses')->where('name', 'Present')->value('id');
        if ($present) {
            foreach (self::TABLES as $tableName) {
                DB::table($tableName)->whereNotNull('attendance_type_id')->whereNull('attendance_status_id')
                    ->update(['attendance_status_id' => $present]);
            }
        }
        // One mark per member per event, so saving twice updates instead of duplicating
        Schema::table('event_attendances', function (Blueprint $table) {
            $table->index(['event_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::table('event_attendances', function (Blueprint $table) {
            $table->dropIndex(['event_id', 'user_id']);
        });
        foreach (self::TABLES as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropConstrainedForeignId('attendance_status_id');
            });
        }
    }
};
