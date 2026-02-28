<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RuleController extends Controller
{
    public function index()
    {
        $rules = Rule::with('trail')->get();

        return response()->json([
            'status' => 'success',
            'data' => $rules
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jalur_id' => 'required|exists:routes,id',
            'description' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 422);
        }

        $rule = Rule::create($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Rule added successfully',
            'data' => $rule->load('trail')
        ], 201);
    }

    public function show($id)
    {
        $rule = Rule::with('trail')->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $rule
        ]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'jalur_id' => 'required|exists:routes,id',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 422);
        }

        $rule = Rule::findOrFail($id);
        $rule->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Rule updated successfully',
            'data' => $rule->load('trail')
        ]);
    }

    public function destroy($id)
    {
        $rule = Rule::findOrFail($id);
        $rule->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Rule deleted successfully'
        ]);
    }

    public function getByTrail($trailId)
    {
        $rules = Rule::where('jalur_id', $trailId)->get();

        return response()->json([
            'status' => 'success',
            'data' => $rules
        ]);
    }
}
