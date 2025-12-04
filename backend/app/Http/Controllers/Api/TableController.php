<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Table;
use App\Helpers\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

/**
 * @OA\Tag(
 *     name="Tables",
 *     description="API untuk manajemen meja"
 * )
 */
class TableController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/tables",
     *     tags={"Tables"},
     *     summary="Dapatkan semua meja",
     *     @OA\Response(
     *         response=200,
     *         description="List meja berhasil diambil"
     *     )
     * )
     */
    public function index()
    {
        $tables = Table::latest()->get();
        return response()->json([
            'success' => true,
            'message' => 'List of tables retrieved successfully',
            'data' => $tables,
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/tables/{id}",
     *     tags={"Tables"},
     *     summary="Dapatkan detail meja",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Detail meja"),
     *     @OA\Response(response=404, description="Meja tidak ditemukan")
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/api/tables",
     *     tags={"Tables"},
     *     summary="Buat meja baru dengan QR code",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"table_number", "capacity"},
     *             @OA\Property(property="table_number", type="string", example="1"),
     *             @OA\Property(property="capacity", type="integer", example=4),
     *             @OA\Property(property="type", type="string", enum={"Indoor", "Outdoor", "VIP"})
     *         )
     *     ),
     *     @OA\Response(response=201, description="Meja berhasil dibuat dengan QR code"),
     *     @OA\Response(response=422, description="Validasi gagal")
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'table_number' => 'required|string|max:50|unique:tables,table_number',
            'capacity' => 'required|integer|min:1',
            'type' => 'nullable|in:Indoor,Outdoor,VIP',
        ]);

        $id = IdGenerator::generate('tables', 'TB');
        $table = Table::create([
            'id' => $id,
            'table_number' => $validated['table_number'],
            'capacity' => $validated['capacity'],
            'type' => $validated['type'] ?? null,
            'status' => 'available',
        ]);

        try {
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
            $qrUrl = "{$frontendUrl}/order/{$id}";

            $qr = new QrCode($qrUrl);
            $writer = new PngWriter();
            $result = $writer->write($qr);

            $qrImagePath = "qrcodes/{$id}.png";
            Storage::disk('public')->put($qrImagePath, $result->getString());

            $table->update([
                'qr_code_url' => asset("storage/{$qrImagePath}"),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Table created successfully with QR code',
                'data' => $table,
            ], 201);
        } catch (\Throwable $ex) {
            return response()->json([
                'success' => false,
                'message' => 'Table created but failed to generate QR code',
                'data' => $table,
            ], 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/tables/{id}",
     *     tags={"Tables"},
     *     summary="Update meja",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="table_number", type="string"),
     *             @OA\Property(property="capacity", type="integer"),
     *             @OA\Property(property="status", type="string", enum={"available", "occupied", "reserved"})
     *         )
     *     ),
     *     @OA\Response(response=200, description="Meja berhasil diupdate"),
     *     @OA\Response(response=404, description="Meja tidak ditemukan")
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="/api/tables/{id}",
     *     tags={"Tables"},
     *     summary="Hapus meja",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Meja berhasil dihapus"),
     *     @OA\Response(response=404, description="Meja tidak ditemukan")
     * )
     */
    public function destroy($id)
    {
        try {
            $table = Table::findOrFail($id);

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
}
