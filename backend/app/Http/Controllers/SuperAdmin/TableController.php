<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TableController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');

        $query = Table::query();

        if ($search) {
            $query->where('table_number', 'like', '%' . $search . '%');
        }

        $tables = $query->latest()->paginate(10);

        return view('superadmin.tables.index', compact('tables'));
    }

    public function create()
    {
        return view('superadmin.tables.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'table_number' => 'required|string|max:255|unique:tables',
            'capacity' => 'required|integer|min:1',
            'type' => 'nullable|string',
            'status' => 'required|in:available,occupied,reserved',
        ]);

        // Set qr_code_url as null for now (can be generated later if needed)
        $validated['qr_code_url'] = null;

        Table::create($validated);

        return redirect()->route('superadmin.tables.index')
            ->with('success', 'Table created successfully!');
    }    public function edit(Table $table)
    {
        return view('superadmin.tables.edit', compact('table'));
    }

    public function update(Request $request, Table $table)
    {
        $validated = $request->validate([
            'table_number' => 'required|string|max:255|unique:tables,table_number,' . $table->id,
            'capacity' => 'required|integer|min:1',
            'type' => 'nullable|string',
            'status' => 'required|in:available,occupied,reserved',
        ]);

        $table->update($validated);

        return redirect()->route('superadmin.tables.index')
            ->with('success', 'Table updated successfully!');
    }    public function destroy(Table $table)
    {
        if ($table->qr_code_url) {
            Storage::disk('public')->delete($table->qr_code_url);
        }

        $table->delete();

        return redirect()->route('superadmin.tables.index')
            ->with('success', 'Table deleted successfully!');
    }
}
