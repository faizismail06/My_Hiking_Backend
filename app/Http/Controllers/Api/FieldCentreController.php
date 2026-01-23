<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FieldCentre;
use Illuminate\Http\Request;

class FieldCentreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $field_centres = FieldCentre::all();
        return response()->json([
            'success' => true,
            'message' => 'Successfully get data on Sports Field Centres',
            'data' => $field_centres,
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(FieldCentre $fieldCentre)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FieldCentre $fieldCentre)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FieldCentre $fieldCentre)
    {
        //
    }
}
