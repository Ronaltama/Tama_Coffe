<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Table;
use App\Helpers\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\ModelNotFoundException;

// Endroid QR Code minimal imports (v6.x)
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class TableController extends Controller
{
    public function index()
    {
        $tables = Table::latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'List of tables retrieved successfully',
            'data' => $tables,
        ], 200);
    }

    public function show($id)
    {
        try {
            $table = Table::findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Table retrieved successfully',
                'data' => $table,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Table not found',
            ], 404);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'table_number' => 'required|string|max:50|unique:tables,table_number',
            'capacity' => 'required|integer|min:1',
            'type' => 'nullable|in:Indoor,Outdoor,VIP',
        ]);

        // 1) generate id
        $id = IdGenerator::generate('tables', 'TB');

        // 2) create DB record first (without qr_code_url)
        $table = Table::create([
            'id' => $id,
            'table_number' => $validated['table_number'],
            'capacity' => $validated['capacity'],
            'type' => $validated['type'] ?? null,
            'status' => 'available',
        ]);

        // 3) make the URL that will be encoded in QR
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
        $qrUrl = "{$frontendUrl}/order/{$id}";

        // 4) generate QR image (minimal, compatible with endroid/qr-code v6)
        try {
            $qr = new QrCode($qrUrl);
            $writer = new PngWriter();
            $result = $writer->write($qr);

            // 5) store QR image
            $qrImagePath = "qrcodes/{$id}.png";
            Storage::disk('public')->put($qrImagePath, $result->getString());

            // 6) update DB with public URL
            $table->update([
                'qr_code_url' => asset("storage/{$qrImagePath}"),
            ]);
        } catch (\Throwable $ex) {
            return response()->json([
                'success' => false,
                'message' => 'Table created but failed to generate QR code: ' . $ex->getMessage(),
                'data' => $table,
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Table created successfully with QR code',
            'data' => $table,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        try {
            $table = Table::findOrFail($id);

            $validated = $request->validate([
                'table_number' => 'sometimes|string|max:50|unique:tables,table_number,' . $id . ',id',
                'capacity' => 'sometimes|integer|min:1',
                'type' => 'nullable|in:Indoor,Outdoor,VIP',
                'status' => 'in:available,occupied,reserved',
            ]);

            $table->update($validated);
            $table->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Table updated successfully',
                'data' => $table,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Table not found',
            ], 404);
        }
    }

    public function destroy($id)
    {
        try {
            $table = Table::findOrFail($id);

            // delete QR file if exists
            if ($table->qr_code_url && str_contains($table->qr_code_url, 'storage/qrcodes/')) {
                $storagePrefix = asset('storage/') . '/';
                $path = str_replace($storagePrefix, '', $table->qr_code_url);

                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }

            $table->delete();

            return response()->json([
                'success' => true,
                'message' => 'Table deleted successfully',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Table not found',
            ], 404);
        }
    }

    public function scanOrder($id)
    {
        try {
            $table = Table::where('id', $id)->firstOrFail();
            return response()->json($table);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Meja tidak ditemukan'], 404);
        }
    }
}
