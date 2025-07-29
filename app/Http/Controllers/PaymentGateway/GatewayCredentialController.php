<?php

namespace App\Http\Controllers\PaymentGateway;
use App\Http\Controllers\Controller;

use App\Models\GatewayCredential;
use Illuminate\Http\Request;

class GatewayCredentialController extends Controller
{

    public function index()
    {
        //
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
    public function store(Request $request)
    {
        // Encrypt
        $encrypted = Crypt::encryptString('your-secret-key');

        // Decrypt
        $decrypted = Crypt::decryptString($encrypted);
    }

    /**
     * Display the specified resource.
     */
    public function show(GatewayCredential $gatewayCredential)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GatewayCredential $gatewayCredential)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, GatewayCredential $gatewayCredential)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GatewayCredential $gatewayCredential)
    {
        //
    }
}
