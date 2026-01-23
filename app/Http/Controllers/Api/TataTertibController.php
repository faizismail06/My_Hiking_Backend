<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TataTertib;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TataTertibController extends Controller
{
    public function index()
    {
        $tataTertibs = TataTertib::with('jalur')->get();

        return response()->json([
            'status' => 'success',
            'data' => $tataTertibs
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jalur_id' => 'required|exists:jalur,id',
            'description' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 422);
        }

        $tataTertib = TataTertib::create($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Tata tertib berhasil ditambahkan',
            'data' => $tataTertib->load('jalur')
        ], 201);
    }

    public function show($id)
    {
        $tataTertib = TataTertib::with('jalur')->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $tataTertib
        ]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'jalur_id' => 'required|exists:jalur,id',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 422);
        }

        $tataTertib = TataTertib::findOrFail($id);
        $tataTertib->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Tata tertib berhasil diperbarui',
            'data' => $tataTertib->load('jalur')
        ]);
    }

    public function destroy($id)
    {
        $tataTertib = TataTertib::findOrFail($id);
        $tataTertib->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Tata tertib berhasil dihapus'
        ]);
    }

    public function getByJalur($jalurId)
    {
        $tataTertibs = TataTertib::with('jalur')
            ->where('jalur_id', $jalurId)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $tataTertibs
        ]);
    }
}
