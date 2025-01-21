<?php

namespace App\Http\Controllers;

use App\Models\BarangInventaris;
use Illuminate\Http\Request;

class BarangInventarisController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = BarangInventaris::all();
        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'jns_brg_kode' => 'required|string|max:5',
                'user_id' => 'required|string|max:10',
                'br_nama' => 'required|string|max:50',
                'br_foto' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Validasi file gambar
                'br_tgl_terima' => 'required|date',
            ]);

            $barangNumber = BarangInventaris::count();

            $barangId = 'BR' . str_pad($barangNumber + 1, 3, '0', STR_PAD_LEFT);

            $imagePath = null;
            if ($request->hasFile('br_foto') && $request->file('br_foto')->isValid()) {
                $image = $request->file('br_foto');

                $filename = $barangId . '.' . $image->getClientOriginalExtension();

                $imagePath = $image->storeAs('images', $filename, 'public');
            }

            $barang = BarangInventaris::create([
                'br_kode' => $barangId,
                'jns_brg_kode' => $data['jns_brg_kode'],
                'user_id' => $data['user_id'],
                'br_nama' => $data['br_nama'],
                'br_foto' => $imagePath,
                'br_tgl_terima' => $data['br_tgl_terima'],
                'br_tgl_entry' => now(),
                'br_status' => 1
            ]);

            return response()->json([
                "message" => "barang berhasil ditambahkan",
                "data" => $barang,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Server Error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $barang = BarangInventaris::find($id);

        if (!$barang) {
            return response()->json(['messeage' => 'Barang tidak ditemukan']);
        }

        return response()->json($barang);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {

            $barang = BarangInventaris::find($id);

            if (!$barang) {
                return response()->json(['message' => 'Barang tidak ditemukan'], 404);
            }

            $validated = $request->validate([
                'jns_brg_kode' => 'required|string|max:5',
                'user_id' => 'required|string|max:10',
                'br_nama' => 'required|string|max:50',
                'br_foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validasi file gambar, 'nullable' agar tidak wajib
                'br_tgl_terima' => 'required|date',
            ]);

            if ($request->hasFile('br_foto') && $request->file('br_foto')->isValid()) {
                if ($barang->br_foto && file_exists(public_path('storage/' . $barang->br_foto))) {
                    unlink(public_path('storage/' . $barang->br_foto));
                }

                $image = $request->file('br_foto');
                $filename = 'BR' . str_pad($id, 3, '0', STR_PAD_LEFT) . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('images', $filename, 'public');

                $validated['br_foto'] = $imagePath;
            }

            $barang->update($validated);

            return response()->json([
                'message' => 'Barang berhasil diperbarui',
                'data' => $barang,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Server Error',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $barang = BarangInventaris::find($id);

        if (!$barang) {
            return response()->json(['messeage' => 'Barang tidak ditemukan']);
        }

        $barang->delete();

        return response()->json(["messeage" => "barang berhasil dihapus"]);
    }
}
