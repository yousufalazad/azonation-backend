<?php
namespace App\Http\Controllers\Org\Asset;

use App\Http\Concerns\ResolvesCurrentOrg;
use App\Http\Concerns\StoresAttachments;
use Illuminate\Routing\Controller;
use App\Models\Asset;
use App\Models\AssetAssignmentLog;
use App\Models\AssetFile;
use App\Models\AssetImage;
use App\Models\OrgMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Things the organisation owns or looks after (equipment, furniture, land, donated goods...).
 * Who holds an asset and its condition are kept as a history in asset_assignment_logs:
 * the active log is the current holder; handing over closes it and starts a new one.
 */
class AssetController extends Controller
{
    use ResolvesCurrentOrg, StoresAttachments;

    private const FILES = ['image' => AssetImage::class, 'file' => AssetFile::class];
    private const FIELDS = [
        'name', 'description', 'start_date', 'end_date', 'quantity', 'value_amount', 'inkind_value', 'privacy_setup_id',
    ];

    public function __construct()
    {
        $this->middleware('org.permission:asset.read')->only(['index', 'show', 'getAssetDetails']);
        $this->middleware('org.permission:asset.create')->only(['create', 'store']);
        $this->middleware('org.permission:asset.update')->only(['edit', 'update', 'handover']);
        $this->middleware('org.permission:asset.delete')->only(['destroy']);
    }

    // The current organisation's assets, each with its current holder and condition
    public function index()
    {
        $current = $this->currentLogs();
        $assets = $this->owned(Asset::class)
            ->select(
                'assets.*',
                'privacy_setups.name as privacy_setup_name',
                'cur.responsible_user_id',
                'cur.assignment_start_date',
                'cur.asset_lifecycle_statuses_id',
                'u.first_name as responsible_user_first_name',
                'u.last_name as responsible_user_last_name',
                'als.name as asset_lifecycle_statuses_name'
            )
            ->leftJoin('privacy_setups', 'assets.privacy_setup_id', '=', 'privacy_setups.id')
            ->leftJoinSub($current, 'cur', 'cur.asset_id', '=', 'assets.id')
            ->leftJoin('users as u', 'cur.responsible_user_id', '=', 'u.id')
            ->leftJoin('asset_lifecycle_statuses as als', 'cur.asset_lifecycle_statuses_id', '=', 'als.id')
            ->orderBy('assets.name')
            ->get();
        return response()->json(['status' => true, 'data' => $assets], 200);
    }

    // One asset with its current holder, full history and attachments
    public function getAssetDetails($assetId)
    {
        $asset = $this->owned(Asset::class)
            ->select('assets.*', 'privacy_setups.name as privacy_setup_name')
            ->leftJoin('privacy_setups', 'assets.privacy_setup_id', '=', 'privacy_setups.id')
            ->where('assets.id', $assetId)
            ->first();
        if (!$asset) {
            return response()->json(['status' => false, 'message' => 'Asset not found'], 404);
        }
        $history = AssetAssignmentLog::query()
            ->where('asset_id', $asset->id)
            ->leftJoin('users as u', 'asset_assignment_logs.responsible_user_id', '=', 'u.id')
            ->leftJoin('asset_lifecycle_statuses as als', 'asset_assignment_logs.asset_lifecycle_statuses_id', '=', 'als.id')
            ->select(
                'asset_assignment_logs.*',
                'u.first_name as responsible_user_first_name',
                'u.last_name as responsible_user_last_name',
                'als.name as asset_lifecycle_statuses_name'
            )
            ->orderByDesc('asset_assignment_logs.is_active')
            ->orderByDesc('asset_assignment_logs.assignment_start_date')
            ->orderByDesc('asset_assignment_logs.id')
            ->get();

        $data = $this->withAttachmentUrls($asset)->toArray();
        $data['history'] = $history;
        $data['current'] = $history->firstWhere('is_active', 1) ?? $history->first();
        return response()->json(['status' => true, 'data' => $data], 200);
    }

    public function show($id)
    {
        return $this->getAssetDetails($id);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules() + $this->handoverRules());
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }
        if ($error = $this->holderError($request->responsible_user_id)) {
            return $error;
        }
        DB::beginTransaction();
        try {
            $asset = new Asset($this->values($request));
            $asset->user_id = $this->orgIdOrFail(); // always the current organisation
            $asset->save();
            // The first holder / condition, if given
            if ($request->filled('responsible_user_id') || $request->filled('asset_lifecycle_statuses_id')) {
                $this->startLog($asset, $request);
            }
            $this->saveAttachments($request, $asset, self::FILES, 'asset_id', 'org/asset');
            DB::commit();
            return response()->json(['status' => true, 'message' => 'Asset created successfully.', 'data' => $asset], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating asset: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'An error occurred. Please try again.'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }
        $asset = $this->owned(Asset::class)->find($id);
        if (!$asset) {
            return response()->json(['status' => false, 'message' => 'Asset not found'], 404);
        }
        $asset->update($this->values($request));
        $this->saveAttachments($request, $asset, self::FILES, 'asset_id', 'org/asset');
        return response()->json(['status' => true, 'message' => 'Asset updated successfully.', 'data' => $asset], 200);
    }

    /**
     * Give the asset to someone (or back to the organisation) and/or record its condition.
     * The current record is closed on the handover date and a new one starts.
     */
    public function handover(Request $request, $id)
    {
        $validator = Validator::make($request->all(), $this->handoverRules());
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }
        $asset = $this->owned(Asset::class)->find($id);
        if (!$asset) {
            return response()->json(['status' => false, 'message' => 'Asset not found'], 404);
        }
        if ($error = $this->holderError($request->responsible_user_id)) {
            return $error;
        }
        DB::transaction(function () use ($asset, $request) {
            $date = $request->assignment_start_date ?: now()->toDateString();
            AssetAssignmentLog::where('asset_id', $asset->id)->where('is_active', 1)
                ->update(['is_active' => 0, 'assignment_end_date' => $date]);
            $this->startLog($asset, $request);
        });
        return response()->json(['status' => true, 'message' => 'Handover saved.'], 201);
    }

    public function destroy($id)
    {
        $asset = $this->owned(Asset::class)->find($id);
        if (!$asset) {
            return response()->json(['status' => false, 'message' => 'Asset not found'], 404);
        }
        DB::transaction(function () use ($asset) {
            AssetAssignmentLog::where('asset_id', $asset->id)->delete();
            $this->deleteAttachments($asset);
            $asset->delete();
        });
        return response()->json(['status' => true, 'message' => 'Asset deleted successfully.'], 200);
    }

    // Latest active holder record per asset (or the latest record if none is active)
    private function currentLogs()
    {
        return DB::table('asset_assignment_logs as l')
            ->select('l.asset_id', 'l.responsible_user_id', 'l.assignment_start_date', 'l.asset_lifecycle_statuses_id')
            ->whereRaw('l.id = (select l2.id from asset_assignment_logs l2 where l2.asset_id = l.asset_id order by l2.is_active desc, l2.id desc limit 1)');
    }

    private function startLog(Asset $asset, Request $request): void
    {
        AssetAssignmentLog::create([
            'asset_id' => $asset->id,
            'responsible_user_id' => $request->responsible_user_id ?: null,
            'assignment_start_date' => $request->assignment_start_date ?: now()->toDateString(),
            'assignment_end_date' => null,
            'asset_lifecycle_statuses_id' => $request->asset_lifecycle_statuses_id ?: null,
            'note' => $request->note,
            'is_active' => 1,
        ]);
    }

    private function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_long_term' => 'nullable|boolean',
            'quantity' => 'nullable|integer|min:0|max:1000000',
            'value_amount' => 'nullable|numeric|min:0|max:9999999999999',
            'inkind_value' => 'nullable|numeric|min:0|max:9999999999999',
            'is_tangible' => 'nullable|boolean',
            'privacy_setup_id' => 'nullable|exists:privacy_setups,id',
            'is_active' => 'nullable|boolean',
        ] + $this->attachmentRules();
    }

    private function handoverRules(): array
    {
        return [
            'responsible_user_id' => 'nullable|integer|exists:users,id',
            'assignment_start_date' => 'nullable|date',
            'asset_lifecycle_statuses_id' => 'nullable|exists:asset_lifecycle_statuses,id',
            'note' => 'nullable|string|max:255',
        ];
    }

    private function values(Request $request): array
    {
        $values = collect(self::FIELDS)->mapWithKeys(fn ($f) => [$f => $request->input($f)])->all();
        $values['quantity'] = (int) ($request->input('quantity') ?: 1);
        $values['is_long_term'] = $request->boolean('is_long_term');
        $values['is_tangible'] = $request->has('is_tangible') ? $request->boolean('is_tangible') : true;
        $values['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;
        return $values;
    }

    // Only members of this organisation can hold its assets (empty = kept by the organisation)
    private function holderError($userId)
    {
        if (!$userId) return null;
        $isMember = OrgMember::where('org_type_user_id', $this->orgIdOrFail())
            ->where('individual_type_user_id', $userId)
            ->exists();
        return $isMember ? null : response()->json([
            'status' => false,
            'message' => 'This person is not a member of your organisation.',
        ], 422);
    }
}
