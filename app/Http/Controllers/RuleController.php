<?php

namespace App\Http\Controllers;
use App\Models\TrailWeb;
use App\Models\Rule;
use Illuminate\Http\Request;

class RuleController extends Controller
{
    public function index()
    {
        $rules = Rule::with('trail')->get();
        return view('rules.index', compact('rules'));
    }

    public function create()
    {
        $trails = TrailWeb::all();
        return view('rules.create', compact('trails'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'jalur_id' => 'required',
            'description' => 'required',
        ]);

        Rule::create($request->all());

        return redirect()->route('rules.index')
            ->with('success', 'Rule created successfully.');
    }

    public function edit(Rule $rule)
    {
        $trails = TrailWeb::all();
        return view('rules.edit', compact('rule', 'trails'));
    }
    
    public function update(Request $request, Rule $rule)
    {
        $request->validate([
            'jalur_id' => 'required',
            'description' => 'required',
        ]);

        $rule->update($request->all());

        return redirect()->route('rules.index')
            ->with('success', 'Rule updated successfully.');
    }

    public function destroy(Rule $rule)
    {
        $rule->delete();

        return redirect()->route('rules.index')
            ->with('success', 'Rule deleted successfully.');
    }
}
