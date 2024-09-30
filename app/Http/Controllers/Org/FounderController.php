<?php
namespace App\Http\Controllers\Org;
use App\Http\Controllers\Controller;
use App\Models\Founder;
use App\Models\User;
use Illuminate\Http\Request;

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
            'name' => 'string',
            'designation' => 'string',
            ]);

        $founder = Founder::create([
            'user_id' => $request->user_id,
            'founder_user_id' => $request->founder_user_id,
            'name' => $request->name,
            'designation' => $request->designation,
            //'status' => 1
        ]);

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
    public function update(Request $request, Founder $founder)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Founder $founder)
    {
        //
    }
}
