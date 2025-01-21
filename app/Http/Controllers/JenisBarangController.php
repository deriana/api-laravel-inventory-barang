<?php

namespace App\Http\Controllers;

use App\Models\JenisBarang;
use Illuminate\Http\Request;

class JenisBarangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = JenisBarang::all();
        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        try {
            $validated = $request->validate([
                'jns_brg_nama' => 'required|string|max:50',
            ]);

            $lastNumber = JenisBarang::count();

            $newKode = 'JB' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);

            $validated['jns_brg_kode'] = $newKode;

            $jenisBarang = JenisBarang::create($validated);

            return response()->json([
                'message' => 'Jenis barang berhasil ditambahkan',
                'data' => $jenisBarang
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
        $jenisBarang = JenisBarang::find($id);

        if (!$jenisBarang) {
            return response()->json(['messeage' => 'jenis barang tidak ditemukan'], 404);
        }

        return response()->json($jenisBarang);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $jenisBarang = JenisBarang::find($id);

        if (!$jenisBarang) {
            return response()->json(['messeage' => "jenis barang tidak ditemukan"], 404);
        }

        $validated = $request->validate(['jns_brg_nama' => 'sometimes|required|string|max:50']);

        $jenisBarang->update($validated);

        return response()->json(["message" => "jenis barang berhasil diperbarui", "data" => $jenisBarang]);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $jenisBarang = JenisBarang::find($id);

        if (!$jenisBarang) {
            return response()->json(['message' => 'jenis barang tidak ditemukan'], 404);
        }

        $jenisBarang->delete();

        return response()->json([
            "message" => "jenis barang berhasil dihapus"
        ]);
    }
}
