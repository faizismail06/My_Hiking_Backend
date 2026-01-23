<?php

namespace App\Http\Controllers;
use App\Models\JalurWeb;
use App\Models\TataTertib;
use Illuminate\Http\Request;

class TataTertibController extends Controller
{
    public function index()
    {
        $tataTermibs = TataTertib::with('jalur')->get();
        return view('tata_tertib.index', compact('tataTermibs'));
    }

    public function create()
    {
        $jalurs = JalurWeb::all();
        return view('tata_tertib.create', compact('jalurs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'jalur_id' => 'required',
            'description' => 'required',
        ]);

        TataTertib::create($request->all());

        return redirect()->route('tata_tertib.index')
            ->with('success', 'Tata Tertib created successfully.');
    }

    public function edit(TataTertib $tataTertib)
    {
        $jalurs = JalurWeb::all();
        return view('tata_tertib.edit', compact('tataTertib', 'jalurs'));
    }
    public function update(Request $request, TataTertib $tataTertib)
    {
        $request->validate([
            'jalur_id' => 'required',
            'description' => 'required',
        ]);

        $tataTertib->update($request->all());

        return redirect()->route('tata_tertib.index')
            ->with('success', 'Tata Tertib updated successfully.');
    }

    public function destroy(TataTertib $tataTertib)
    {
        $tataTertib->delete();

        return redirect()->route('tata_tertib.index')
            ->with('success', 'Tata Tertib deleted successfully.');
    }
}