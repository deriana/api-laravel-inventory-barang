<?php

namespace App\Http\Controllers;

use App\Models\BarangInventaris;
use App\Models\Peminjaman;
use App\Models\PeminjamanBarang;
use App\Models\Pengembalian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PeminjamanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $peminjaman = Peminjaman::with('barang')->get();

        return response()->json([
            'status' => 'success',
            'data' => $peminjaman,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'user_id' => 'required|string|max:10',
                'pb_no_siswa' => 'required|string|max:50',
                'pb_nama_siswa' => 'required|string|max:100',
                'pb_harus_kembali_tgl' => 'required|date',
                'barang' => 'required|array',
                'barang.*.br_kode' => 'required|string|max:12',
                'barang.*.pdb_tgl' => 'required|date',
            ]);

            $validated['pb_tgl'] = now();
            $validated['pb_stat'] = 1;

            $lastNumber = Peminjaman::count();
            $newPbId = 'PB' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
            $validated['pb_id'] = $newPbId;

            $peminjaman = Peminjaman::create($validated);

            foreach ($validated['barang'] as $barang) {
                $lastPbdNumber = PeminjamanBarang::count();
                $newPbdId = 'PBD' . str_pad($lastPbdNumber + 1, 3, '0', STR_PAD_LEFT);

                PeminjamanBarang::create([
                    'pbd_id' => $newPbdId,
                    'pb_id' => $newPbId,
                    'br_kode' => $barang['br_kode'],
                    'pdb_tgl' => $barang['pdb_tgl'],
                    'pdb_sts' => 1,
                ]);

                BarangInventaris::where('br_kode', $barang['br_kode'])
                    ->update(['br_status' => '0']);
            }

            $lastKembaliId = Pengembalian::max('kembali_id'); 
            $nextIdNumber = (int) substr($lastKembaliId, 2);
            $newKembaliId = 'KB' . str_pad($nextIdNumber + 1, 3, '0', STR_PAD_LEFT);
            Pengembalian::create([
                'kembali_id' => $newKembaliId,
                'pb_id' => $newPbId,
                'user_id' => $validated['user_id'],
                'kembali_tgl' => null,
                'kembali_sts' => 0,
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Peminjaman berhasil dibuat dan pengembalian dicatat',
                'data' => $peminjaman->load('barangInventaris'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat membuat peminjaman',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $peminjaman = Peminjaman::with('barang')->find($id);

        if (!$peminjaman) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $peminjaman
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        DB::beginTransaction();
    
        try {
            $validated = $request->validate([
                'kembali_tgl' => 'required|date', 
            ]);
    
            $peminjaman = Peminjaman::where('pb_id', $id)->first();
    
            if (!$peminjaman) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Peminjaman dengan ID tersebut tidak ditemukan',
                ], 404);
            }
    
            $pengembalian = Pengembalian::where('pb_id', $id)->first();
    
            if (!$pengembalian) {
                $newKembaliId = 'KB' . str_pad(Pengembalian::count() + 1, 3, '0', STR_PAD_LEFT);
    
                $pengembalian = Pengembalian::create([
                    'kembali_id' => $newKembaliId,
                    'pb_id' => $id,
                    'user_id' => $peminjaman->user_id,
                    'kembali_tgl' => $validated['kembali_tgl'],
                    'kembali_sts' => 0, 
                ]);
            } else {
                $pengembalian->update([
                    'kembali_tgl' => $validated['kembali_tgl'],
                    'kembali_sts' => 1, 
                ]);
            }
    
            $barangPeminjaman = PeminjamanBarang::where('pb_id', $id)->get();
            foreach ($barangPeminjaman as $peminjamanBarang) {
                $barang = BarangInventaris::where('br_kode', $peminjamanBarang->br_kode)->first();
                if ($barang) {
                    $barang->update([
                        'br_status' => 1, 
                    ]);
                }
            }
    
            $peminjaman->update([
                'pb_stat' => 0, 
            ]);
    
            PeminjamanBarang::where('pb_id', $id)->update([
                'pdb_sts' => 0, 
            ]);
    
            DB::commit();
    
            return response()->json([
                'status' => 'success',
                'message' => 'Peminjaman dan pengembalian berhasil diperbarui',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack(); 
    
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengupdate pengembalian',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::beginTransaction(); 
    
        try {
            PeminjamanBarang::where('pb_id', $id)->delete();
    
            Pengembalian::where('pb_id', $id)->delete();
    
            $peminjaman = Peminjaman::findOrFail($id);  
            $peminjaman->delete();
    
            DB::commit();
    
            return response()->json([
                'status' => 'success',
                'message' => 'Data peminjaman beserta seluruh kaitannya berhasil dihapus.',
            ], 200);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menghapus data peminjaman.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
}
