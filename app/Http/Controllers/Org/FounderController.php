<?php

namespace App\Http\Controllers\Org;

use App\Http\Concerns\ResolvesCurrentOrg;
use App\Http\Controllers\Controller;
use App\Models\Founder;
use App\Models\FounderProfileImage;
use App\Models\OrgMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

/**
 * The people who founded the organisation. A founder is either linked to a member's
 * account (founder_user_id) or entered by name for someone without an account.
 */
class FounderController extends Controller
{
    use ResolvesCurrentOrg;

    public function index()
    {
        $founders = $this->owned(Founder::class)
            ->leftJoin('users', 'founders.founder_user_id', '=', 'users.id')
            ->select('founders.*', 'users.first_name as member_first_name', 'users.last_name as member_last_name')
            ->with(['founder_image', 'user_image'])
            ->orderBy('founders.id')
            ->get()
            ->map(fn ($f) => $this->present($f));
        return response()->json(['status' => true, 'data' => $founders], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }
        if ($error = $this->personError($request)) {
            return $error;
        }
        $founder = DB::transaction(function () use ($request) {
            $founder = new Founder($this->values($request));
            $founder->user_id = $this->orgIdOrFail(); // always the current organisation
            $founder->save();
            $this->savePhoto($request, $founder);
            return $founder;
        });
        return response()->json(['status' => true, 'message' => 'Founder added successfully', 'data' => $founder], 201);
    }

    public function create() {}
    public function show($id) {}
    public function edit($id) {}

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), $this->rules());
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }
        $founder = $this->owned(Founder::class)->find($id);
        if (!$founder) {
            return response()->json(['status' => false, 'message' => 'Founder not found'], 404);
        }
        if ($error = $this->personError($request)) {
            return $error;
        }
        DB::transaction(function () use ($request, $founder) {
            $founder->update($this->values($request));
            $this->savePhoto($request, $founder);
        });
        return response()->json(['status' => true, 'message' => 'Founder updated successfully.', 'data' => $founder], 200);
    }

    public function destroy($id)
    {
        $founder = $this->owned(Founder::class)->find($id);
        if (!$founder) {
            return response()->json(['status' => false, 'message' => 'Founder not found'], 404);
        }
        $this->removePhotos($founder);
        $founder->delete();
        return response()->json(['status' => true, 'message' => 'Founder deleted successfully.'], 200);
    }

    // Only what the page shows; the linked member's own photo is used if the founder has none
    private function present(Founder $f): array
    {
        $memberName = trim(($f->member_first_name ?? '') . ' ' . ($f->member_last_name ?? ''));
        $ownName = in_array($f->full_name, [null, '', 'null'], true) ? '' : $f->full_name;
        $photo = $f->founder_image?->file_path ? $f->founder_image->file_path : $f->user_image?->image_path;
        return [
            'id' => $f->id,
            'founder_user_id' => $f->founder_user_id,
            'name' => $memberName ?: $ownName,
            'full_name' => $ownName,
            'is_member' => (bool) $f->founder_user_id,
            'designation' => $f->designation,
            'email' => $f->email,
            'mobile' => $f->mobile,
            'address' => $f->address,
            'note' => $f->note,
            'is_active' => (int) $f->is_active,
            'image_url' => $photo ? url(Storage::url($photo)) : null,
            'has_own_photo' => (bool) $f->founder_image,
        ];
    }

    private function rules(): array
    {
        return [
            'founder_user_id' => 'nullable|integer|exists:users,id',
            'full_name' => 'nullable|string|max:100',
            'designation' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:50',
            'mobile' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'profile_image' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:5120',
        ];
    }

    private function values(Request $request): array
    {
        return [
            'founder_user_id' => $request->founder_user_id ?: null,
            // Linked members show their account name; the name is only kept for people without one
            'full_name' => $request->founder_user_id ? null : $request->full_name,
            'designation' => $request->designation,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'address' => $request->address,
            'note' => $request->note,
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
        ];
    }

    // A founder is either one of the organisation's members or someone named by hand
    private function personError(Request $request)
    {
        if ($request->founder_user_id) {
            $isMember = OrgMember::where('org_type_user_id', $this->orgIdOrFail())
                ->where('individual_type_user_id', $request->founder_user_id)
                ->exists();
            return $isMember ? null : response()->json(['status' => false, 'message' => 'This person is not a member of your organisation.'], 422);
        }
        return trim((string) $request->full_name) === ''
            ? response()->json(['status' => false, 'message' => 'Choose a member or enter the founder\'s name.'], 422)
            : null;
    }

    // A new photo replaces the old one (before, the first photo was always shown)
    private function savePhoto(Request $request, Founder $founder): void
    {
        if (!$request->hasFile('profile_image')) return;
        $this->removePhotos($founder);
        $image = $request->file('profile_image');
        $path = $image->storeAs('org/founder-profile/image', Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(), 'public');
        FounderProfileImage::create([
            'founder_id' => $founder->id,
            'file_path' => $path,
            'file_name' => $image->getClientOriginalName(),
            'mime_type' => $image->getClientMimeType(),
            'file_size' => $image->getSize(),
            'is_public' => true,
            'is_active' => true,
        ]);
    }

    private function removePhotos(Founder $founder): void
    {
        $photos = FounderProfileImage::where('founder_id', $founder->id)->get();
        foreach ($photos as $photo) {
            Storage::disk('public')->delete($photo->file_path);
        }
        FounderProfileImage::where('founder_id', $founder->id)->delete();
    }
}
