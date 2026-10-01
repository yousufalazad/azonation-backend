<?php

namespace App\Http\Controllers\Org\Membership;

use App\Http\Concerns\ResolvesCurrentOrg;
use Illuminate\Routing\Controller;
use App\Models\OrgMember;
use App\Models\OrgMembershipRenewal;
use App\Models\OrgMembershipRenewalCycle;
use App\Models\OrgMembershipRenewalPrice;
use App\Models\OrgMembershipType;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Membership renewals: one record per member per paid period.
 * The overview works out, for every active member, until when they have paid
 * and whether they are paid up, due soon, overdue or have nothing recorded.
 */
class OrgMembershipRenewalController extends Controller
{
    use ResolvesCurrentOrg;

    // A renewal is "due soon" this many days before the paid period ends
    private const DUE_SOON_DAYS = 30;

    public function __construct()
    {
        $this->middleware('org.permission:org-membership-renewal.read')->only(['index', 'show', 'overview']);
        $this->middleware('org.permission:org-membership-renewal.create')->only(['store']);
        $this->middleware('org.permission:org-membership-renewal.update')->only(['update']);
        $this->middleware('org.permission:org-membership-renewal.delete')->only(['destroy']);
    }

    // Renewal records, newest first; ?individual_type_user_id= for one member's history
    public function index(Request $request)
    {
        $renewals = $this->owned(OrgMembershipRenewal::class, 'org_type_user_id')
            ->when($request->filled('individual_type_user_id'), fn ($q) => $q->where('individual_type_user_id', $request->individual_type_user_id))
            ->leftJoin('membership_renewal_cycles', 'membership_renewal_cycles.id', '=', 'org_membership_renewals.membership_renewal_cycle_id')
            ->orderByDesc('period_end')
            ->orderByDesc('org_membership_renewals.id')
            ->get(['org_membership_renewals.*', 'membership_renewal_cycles.name as cycle_name']);

        return response()->json(['status' => true, 'data' => $renewals]);
    }

    public function overview()
    {
        $orgId = $this->orgIdOrFail();
        $today = Carbon::today();

        $members = OrgMember::query()
            ->where('org_members.org_type_user_id', $orgId)
            ->where('org_members.is_active', 1)
            ->join('users', 'users.id', '=', 'org_members.individual_type_user_id')
            ->leftJoin('membership_types', 'membership_types.id', '=', 'org_members.membership_type_id')
            ->get([
                'org_members.id as org_member_id',
                'org_members.individual_type_user_id as user_id',
                'org_members.existing_membership_id',
                'org_members.membership_type_id',
                'org_members.membership_start_date',
                'users.first_name', 'users.last_name', 'users.azon_id',
                'membership_types.name as membership_type',
            ]);

        // Latest paid period per member, in one query
        $latest = OrgMembershipRenewal::where('org_type_user_id', $orgId)
            ->where('status', 'completed')
            ->whereIn('individual_type_user_id', $members->pluck('user_id'))
            ->orderBy('period_end')
            ->get(['individual_type_user_id', 'period_end', 'amount_paid', 'renewed_at', 'membership_renewal_cycle_id'])
            ->keyBy('individual_type_user_id');

        $images = DB::table('profile_images')->whereIn('user_id', $members->pluck('user_id'))->pluck('image_path', 'user_id');

        $cycles = OrgMembershipRenewalCycle::where('org_type_user_id', $orgId)->where('is_active', 1)
            ->with('memberRenewalCycle:id,name,duration_in_months')->get();

        // Fees currently valid, by platform membership type
        $orgTypes = OrgMembershipType::where('org_type_user_id', $orgId)->pluck('membership_type_id', 'id');
        $fees = OrgMembershipRenewalPrice::where('org_type_user_id', $orgId)->where('is_active', 1)
            ->where(fn ($q) => $q->whereNull('valid_from')->orWhere('valid_from', '<=', $today))
            ->where(fn ($q) => $q->whereNull('valid_to')->orWhere('valid_to', '>=', $today))
            ->get(['org_membership_type_id', 'org_mem_renewal_cycle_id', 'currency', 'unit_amount_minor'])
            ->map(fn ($p) => [
                'membership_type_id' => $orgTypes[$p->org_membership_type_id] ?? null,
                'org_cycle_id' => $p->org_mem_renewal_cycle_id,
                'currency' => $p->currency,
                'amount' => $p->unit_amount_minor / 100,
            ]);

        $rows = $members->map(function ($m) use ($latest, $images, $today) {
            $last = $latest[$m->user_id] ?? null;
            $paidUntil = $last?->period_end ? Carbon::parse($last->period_end)->startOfDay() : null;
            $state = match (true) {
                !$paidUntil => 'not_recorded',
                $paidUntil->lt($today) => 'overdue',
                $paidUntil->lte($today->copy()->addDays(self::DUE_SOON_DAYS)) => 'due_soon',
                default => 'paid',
            };
            $path = $images[$m->user_id] ?? null;

            return [
                'org_member_id' => $m->org_member_id,
                'user_id' => $m->user_id,
                'name' => trim(($m->first_name ?? '') . ' ' . ($m->last_name ?? '')) ?: $m->azon_id,
                'membership_id' => $m->existing_membership_id,
                'membership_type_id' => $m->membership_type_id,
                'membership_type' => $m->membership_type,
                'joined_on' => $m->membership_start_date ? Carbon::parse($m->membership_start_date)->toDateString() : null,
                'paid_until' => $paidUntil?->toDateString(),
                'last_amount' => $last?->amount_paid,
                'last_paid_on' => $last?->renewed_at ? Carbon::parse($last->renewed_at)->toDateString() : null,
                'last_cycle_id' => $last?->membership_renewal_cycle_id,
                'days_left' => $paidUntil ? $today->diffInDays($paidUntil, false) : null,
                'state' => $state,
                'image_url' => $path ? url(Storage::url($path)) : null,
            ];
        })->values();

        return response()->json([
            'status' => true,
            'data' => [
                'members' => $rows,
                'cycles' => $cycles->map(fn ($c) => [
                    'id' => $c->id,
                    'member_renewal_cycle_id' => $c->member_renewal_cycle_id,
                    'name' => $c->memberRenewalCycle?->name,
                    'months' => (float) ($c->memberRenewalCycle?->duration_in_months ?? 12),
                    'alignment' => $c->alignment,
                    'anchor_month' => $c->anchor_month,
                    'anchor_day' => $c->anchor_day,
                    'grace_days' => $c->grace_days,
                ])->values(),
                'fees' => $fees->values(),
                'due_soon_days' => self::DUE_SOON_DAYS,
            ],
        ]);
    }

    // Record a paid renewal for a member (the organisation took the payment)
    public function store(Request $request)
    {
        $orgId = $this->orgIdOrFail();
        $data = $this->validated($request, $orgId);

        $renewal = OrgMembershipRenewal::create($data + [
            'org_type_user_id' => $orgId,
            'status' => 'completed',
            'initiated_by' => 'organisation',
            'initiated_source' => 'manual',
            'attempt_count' => 0,
        ]);

        return response()->json(['status' => true, 'data' => $renewal]);
    }

    public function show($id)
    {
        $renewal = $this->owned(OrgMembershipRenewal::class, 'org_type_user_id')->findOrFail($id);

        return response()->json(['status' => true, 'data' => $renewal]);
    }

    // Correct a recorded renewal (dates, amount, note); who it is for does not change
    public function update(Request $request, $id)
    {
        $renewal = $this->owned(OrgMembershipRenewal::class, 'org_type_user_id')->findOrFail($id);
        $request->merge(['individual_type_user_id' => $renewal->individual_type_user_id]);
        $renewal->update($this->validated($request, $renewal->org_type_user_id));

        return response()->json(['status' => true, 'data' => $renewal]);
    }

    public function destroy($id)
    {
        $this->owned(OrgMembershipRenewal::class, 'org_type_user_id')->findOrFail($id)->delete();

        return response()->json(['status' => true]);
    }

    private function validated(Request $request, int $orgId): array
    {
        $data = $request->validate([
            'individual_type_user_id' => 'required|integer',
            'membership_renewal_cycle_id' => 'required|integer',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'amount_paid' => 'required|numeric|min:0|max:999999.99',
            'renewed_at' => 'nullable|date',
            'org_notes' => 'nullable|string|max:255',
        ]);

        $isMember = OrgMember::where('org_type_user_id', $orgId)->where('individual_type_user_id', $data['individual_type_user_id'])->exists();
        abort_unless($isMember, 422, 'This person is not a member of your organisation.');

        $offered = OrgMembershipRenewalCycle::where('org_type_user_id', $orgId)->where('member_renewal_cycle_id', $data['membership_renewal_cycle_id'])->exists();
        abort_unless($offered, 422, 'Your organisation does not offer this renewal period.');

        $data['period_start'] = Carbon::parse($data['period_start'])->toDateString();
        $data['period_end'] = Carbon::parse($data['period_end'])->toDateString();
        $data['renewed_at'] = !empty($data['renewed_at']) ? Carbon::parse($data['renewed_at'])->toDateString() : Carbon::today()->toDateString();

        return $data;
    }
}
