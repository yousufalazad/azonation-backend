<?php
namespace App\Http\Controllers\Org;
use App\Http\Controllers\Controller;
use App\Models\Founder;
use App\Models\FounderProfileImage;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class FounderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($userId)
    {
        $founders = Founder::where('user_id', $userId)
        ->with('founders')
        ->get();

    return response()->json([
        'status' => true,
        'data' => $founders
    ]);
    }

    
    public function search(Request $request)
    {
        $query = $request->input('query');

        $results = User::where('type', 'individual') // Filter by user type
            ->where(function ($q) use ($query) { // Group search conditions
                $q->where('azon_id', 'like', "%{$query}%")
                    ->orWhere('name', 'like', "%{$query}%")
                    ->orWhere('username', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%")
                    ->orWhereRaw("CONCAT(dialing_codes.dialing_code, phone_numbers.phone_number) LIKE ?", ["%{$query}%"]); // Search by full phone number
            })
            ->leftJoin('addresses', 'addresses.user_id', '=', 'users.id') // Left join addresses table
            ->leftJoin('countries', 'countries.id', '=', 'addresses.country_id') // Left join countries table
            ->leftJoin('phone_numbers', 'phone_numbers.user_id', '=', 'users.id') // Left join phone_numbers table
            ->leftJoin('dialing_codes', 'dialing_codes.id', '=', 'phone_numbers.dialing_code_id') // Left join dialing_codes table
            ->select(
                'users.*',
                'addresses.city',
                'countries.country_name',
                'dialing_codes.dialing_code',
                'phone_numbers.phone_number'
            )
            ->get();

        return response()->json([
            'status' => true,
            'data' => $results
        ]);
    }



    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'exists:users,id',
            'founder_user_id' => 'nullable',
            'name' => 'string|max:50',
            'designation' => 'string|max:50',
            'is_active' => 'boolean',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:7168',
            ]);

        $founder = Founder::create([
            'user_id' => $request->user_id,
            'founder_user_id' => $request->founder_user_id,
            'name' => $request->name,
            'designation' => $request->designation,
            'is_active' => $request->is_active,
        ]);


        // // Handle multiple image uploads
        if ($request->hasFile('profile_image')) {
            foreach ($request->file('profile_image') as $image) {
                $imagePath = $image->storeAs(
                    'org/image/founder-profile-image',
                    Carbon::now()->format('YmdHis') . '_' . $image->getClientOriginalName(),
                    'public'
                );
                dd($imagePath);

                FounderProfileImage::create([
                    'founder_id' => $founder->id,
                    'file_path' => $imagePath, // Store the document path
                    'file_name' => $image->getClientOriginalName(), // Store the document name
                    'mime_type' => $image->getClientMimeType(), // Store the MIME type
                    'file_size' => $image->getSize(), // Store the size of the document
                    'is_public' => true, // Set the document as public
                    'is_active' => true, // Set the document as active
                ]);
            }
        }

        // Retrieve the individual user and the organization name
        // $individualUser = User::find($validated['individual_type_user_id']);
        // $orgUser = User::find($validated['org_type_user_id']);
        // $orgName = $orgUser ? $orgUser->name : 'The Organization'; // Adjust according to your org naming conventions

        // if ($individualUser) {
        //     // Send the email to the individual user
        //     Mail::to($individualUser->email)->send(new AddMemberSuccessMail($individualUser->name, $orgName));
        // }

        // User::find($individualUser->id)->notify(new AddMemberSuccess($orgName));


        return response()->json([
            'status' => true,
            'message' => 'founder added successfully',
            'data' => $founder,
        ]);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    // public function store(Request $request)
    // {
    //     //
    // }

    /**
     * Display the specified resource.
     */
    public function show(Founder $founder)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Founder $founder)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    // public function update(Request $request, Founder $founder)
    // {
    //     //
    // }

    public function update(Request $request, $id)
    {
        // Validate the incoming request
        $request->validate([
            'name' => 'string|max:50',
            'designation' => 'string|max:50',
            'is_active' => 'boolean',
        ]);

        try {
            // Find the founder by ID
            $founder = Founder::findOrFail($id);

            // Update the founder's designation
            $founder->name = $request->input('name');
            $founder->designation = $request->input('designation');
            $founder->is_active = $request->input('is_active');
            $founder->save();

            return response()->json([
                'status' => true,
                'message' => 'Founder designation updated successfully.',
                'data' => $founder
            ], 200);

        } catch (\Exception $e) {
            // Handle the error and send response
            return response()->json([
                'status' => false,
                'message' => 'Failed to update founder designation.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Founder $founder)
    {
        //
    }
}
