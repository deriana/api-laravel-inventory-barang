<?php

namespace App\Http\Controllers;

use App\Models\BarangInventaris;
use App\Models\Pengembalian;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function laporanDaftarBarang()
    {
        $data = BarangInventaris::with('jenisBarang')->get();

        $laporan = $data->map(function ($item) {
            return [
                'Kode Barang' => $item->br_kode,
                'Jenis Barang' => optional($item->jenisBarang)->jns_brg_nama,
                'Nama Barang' => $item->br_nama,
                'Foto' => $item->br_foto,
                'Tanggal Terima' => $item->br_tgl_terima,
                'Tanggal Entry' => $item->br_tgl_entry,
                'Status' => $item->br_status,
            ];
        });

        return response()->json($laporan);
    }

    public function laporanStatusBarang()
    {
        $data = BarangInventaris::with('jenisBarang')->get();

        $laporan = $data->map(function ($item) {
            return [
                'Kode Barang' => $item->br_kode,
                'Nama Barang' => $item->br_nama,
                'Jenis Barang' => optional($item->jenisBarang)->jns_brg_nama,
                'Status Barang' => $item->br_status,
            ];
        });

        return response()->json($laporan);
    }

    public function laporanPengembalianBarang()
    {
        $data = Pengembalian::with(['peminjaman', 'peminjaman.barangInventaris'])->get();

        $laporan = $data->map(function ($item) {
            return [
                'ID Pengembalian' => $item->kembali_id,
                'ID Peminjaman' => $item->pb_id,
                'Tanggal Pengembalian' => $item->kembali_tgl,
                'Status Pengembalian' => $item->kembali_sts,
                'Barang' => $item->peminjaman->barangInventaris->map(function ($barang) {
                    return [
                        'Kode Barang' => $barang->br_kode,
                        'Nama Barang' => $barang->br_nama,
                    ];
                }),
            ];
        });

        return response()->json($laporan);
    }
}
