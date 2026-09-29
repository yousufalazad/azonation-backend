<?php

namespace App\Http\Concerns;

use App\Models\AttendanceStatus;

/**
 * Rules shared by member and guest attendance.
 */
trait AttendanceRules
{
    // status id => did the person take part (false for Absent, Excused, No Show...)
    protected function attendedMap(): array
    {
        return AttendanceStatus::pluck('is_attended', 'id')->map(fn ($v) => (bool) $v)->all();
    }

    protected function needTypeResponse(string $field)
    {
        return response()->json([
            'status' => false,
            'message' => 'Choose how the person attended (for example In Person or Online).',
            'errors' => [$field => ['Choose how the person attended.']],
        ], 422);
    }

    // is_active columns are enum('0','1') or tinyint: '1'/'0' works for both
    // (a bare 1/true on an enum would pick its first value, '0')
    protected function activeValue($value): string
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) === false ? '0' : '1';
    }
}
