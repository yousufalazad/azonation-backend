<?php

namespace App\Services;

use App\Models\MemberFamilyPerson;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * What an organisation may see of its members' families.
 * Only current members who chose to share count; "numbers" shows counts per member,
 * "details" adds names and the rest. Leaving the organisation hides it straight away.
 */
class MemberFamilies
{
    public const AGE_GROUPS = ['0-4' => [0, 4], '5-12' => [5, 12], '13-17' => [13, 17], '18-59' => [18, 59], '60+' => [60, 200]];
    public const ADULT_AGE = 18;

    public static function age(?int $birthYear): ?int
    {
        return $birthYear ? max(0, (int) now()->year - $birthYear) : null;
    }

    public static function ageGroup(?int $birthYear): ?string
    {
        $age = self::age($birthYear);
        if ($age === null) {
            return null;
        }
        foreach (self::AGE_GROUPS as $key => [$from, $to]) {
            if ($age >= $from && $age <= $to) {
                return $key;
            }
        }
        return null;
    }

    // Current members of the organisation who share with it: user_id => level
    public static function shares(int $orgId): Collection
    {
        return DB::table('member_family_shares as s')
            ->join('org_members as m', function ($join) use ($orgId) {
                $join->on('m.individual_type_user_id', '=', 's.user_id')
                    ->where('m.org_type_user_id', '=', $orgId)
                    ->where('m.is_active', '=', '1');
            })
            ->where('s.org_id', $orgId)
            ->distinct()
            ->pluck('s.level', 's.user_id');
    }

    public static function activeMemberCount(int $orgId): int
    {
        return DB::table('org_members')->where('org_type_user_id', $orgId)->where('is_active', '1')
            ->distinct()->count('individual_type_user_id');
    }

    // Counts for a list of people: total, adults, children, age not given, by age group
    public static function counts(Collection $people): array
    {
        $groups = array_fill_keys(array_keys(self::AGE_GROUPS), 0);
        $adults = $children = $unknown = 0;
        foreach ($people as $p) {
            $age = self::age($p->birth_year);
            if ($age === null) {
                $unknown++;
                continue;
            }
            $age >= self::ADULT_AGE ? $adults++ : $children++;
            $group = self::ageGroup($p->birth_year);
            if ($group) {
                $groups[$group]++;
            }
        }
        return ['total' => $people->count(), 'adults' => $adults, 'children' => $children, 'unknown_age' => $unknown, 'age_groups' => $groups];
    }

    // Totals only (no names): used for an event's headcount estimate
    public static function estimate(int $orgId): array
    {
        $shares = self::shares($orgId);
        $people = MemberFamilyPerson::whereIn('user_id', $shares->keys())->get(['birth_year']);
        return self::counts($people) + [
            'active_members' => self::activeMemberCount($orgId),
            'sharing_members' => $shares->count(),
        ];
    }

    // The Member families page: totals plus one row per sharing member
    public static function summary(int $orgId): array
    {
        $shares = self::shares($orgId);
        $people = MemberFamilyPerson::whereIn('user_id', $shares->keys())->orderBy('birth_year')->get()->groupBy('user_id');
        $members = DB::table('users')->whereIn('id', $shares->keys())->get(['id', 'first_name', 'last_name', 'azon_id'])->keyBy('id');

        $families = $shares->map(function ($level, $userId) use ($people, $members) {
            $mine = $people->get($userId, collect());
            $member = $members->get($userId);
            return [
                'member_id' => (int) $userId,
                'name' => trim(($member->first_name ?? '') . ' ' . ($member->last_name ?? '')),
                'azon_id' => $member->azon_id ?? null,
                'level' => $level,
                'counts' => self::counts($mine),
                'people' => $level === 'details'
                    ? $mine->map(fn ($p) => [
                        'name' => $p->name,
                        'relationship' => $p->relationship,
                        'birth_year' => $p->birth_year,
                        'age_group' => self::ageGroup($p->birth_year),
                        'gender' => $p->gender,
                        'note' => $p->note,
                    ])->values()
                    : null,
            ];
        })->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->values();

        return [
            'totals' => self::counts($people->flatten(1)) + [
                'active_members' => self::activeMemberCount($orgId),
                'sharing_members' => $shares->count(),
            ],
            'families' => $families,
        ];
    }
}
