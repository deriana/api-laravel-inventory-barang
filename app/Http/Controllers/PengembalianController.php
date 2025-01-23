<?php

namespace App\Http\Controllers;

use App\Models\Pengembalian;
use App\Models\Peminjaman;
use App\Models\PeminjamanBarang;
use App\Models\BarangInventaris;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PengembalianController extends Controller
{
    /**
     * Display a listing of the resource (Optional, based on your needs).
     */
    public function index()
    {
        // This will return all pengembalian records, you can modify it as needed
        $pengembalians = Pengembalian::all()  ;
        return response()->json([
            'status' => 'success',
            'data' => $pengembalians,
        ], 200);
    }

    /**
     * Store a newly created resource in storage (Handles returning of borrowed items).
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            // Validate input data
            $validated = $request->validate([
                'pb_id' => 'required|string|max:12|exists:tm_peminjaman,pb_id',
                'user_id' => 'required|string|max:10',
                'kembali_tgl' => 'required|date',
            ]);

            // Fetch peminjaman data based on the pb_id
            $peminjaman = Peminjaman::where('pb_id', $validated['pb_id'])->first();
            if (!$peminjaman) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Peminjaman not found',
                ], 404);
            }

            // Mark the peminjaman as returned by updating its status (optional)
            $peminjaman->update([
                'pb_stat' => 1,  // Assuming 2 represents returned status, you can adjust the status based on your model
            ]);

            // Create a new Pengembalian record
            $lastKembaliId = Pengembalian::max('kembali_id');
            $nextIdNumber = (int) substr($lastKembaliId, 2);
            $newKembaliId = 'KB' . str_pad($nextIdNumber + 1, 3, '0', STR_PAD_LEFT);

            $pengembalian = Pengembalian::create([
                'kembali_id' => $newKembaliId,
                'pb_id' => $validated['pb_id'],
                'user_id' => $validated['user_id'],
                'kembali_tgl' => $validated['kembali_tgl'],
                'kembali_sts' => 1,  // Assuming '1' means the item has been returned
            ]);

            // Mark the related barang as available again (i.e. update status to 1, assuming 1 means available)
            $peminjamanBarang = PeminjamanBarang::where('pb_id', $validated['pb_id'])->get();
            foreach ($peminjamanBarang as $item) {
                BarangInventaris::where('br_kode', $item->br_kode)
                    ->update(['br_status' => '1']);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Pengembalian berhasil dibuat',
                'data' => $pengembalian,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat membuat pengembalian',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $pengembalian = Pengembalian::find($id);

        if (!$pengembalian) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pengembalian not found',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $pengembalian,
        ], 200);
    }

    /**
     * Update the specified resource in storage (Not needed in your case since we're updating Peminjaman status in `store`).
     */
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        DB::beginTransaction();

        try {
            // Validate input data
            $validated = $request->validate([
                'kembali_tgl' => 'nullable|date',
                'kembali_sts' => 'nullable|in:0,1', // Assuming 0 = not returned, 1 = returned
            ]);

            // Fetch the Pengembalian record
            $pengembalian = Pengembalian::where('kembali_id', $id)->first();

            if (!$pengembalian) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pengembalian not found',
                ], 404);
            }

            // Update the return date (if provided)
            if (isset($validated['kembali_tgl'])) {
                $pengembalian->update([
                    'kembali_tgl' => $validated['kembali_tgl'],
                ]);
            }

            // Update the return status (if provided)
            if (isset($validated['kembali_sts'])) {
                $pengembalian->update([
                    'kembali_sts' => $validated['kembali_sts'],
                ]);
            }

            // If the return status has changed to "returned" (1), update the related barang status
            if (isset($validated['kembali_sts']) && $validated['kembali_sts'] == 1) {
                $peminjamanBarang = PeminjamanBarang::where('pb_id', $pengembalian->pb_id)->get();

                foreach ($peminjamanBarang as $item) {
                    BarangInventaris::where('br_kode', $item->br_kode)
                        ->update(['br_status' => '1']);  // Set the barang as available again
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Pengembalian berhasil diperbarui',
                'data' => $pengembalian,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat memperbarui pengembalian',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // If you need to handle deletion, you can implement logic to delete Pengembalian records
        $pengembalian = Pengembalian::where('kembali_id', $id)->first();
        if ($pengembalian) {
            $pengembalian->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Pengembalian berhasil dihapus',
            ], 200);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Pengembalian tidak ditemukan',
        ], 404);
    }
}
