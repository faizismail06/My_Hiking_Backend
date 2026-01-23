<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    // Menampilkan daftar pembayaran
    public function index()
    {
        // Gunakan paginate untuk membatasi jumlah data per halaman (misalnya 10 per halaman)
        $payments = Payment::paginate(10);

        // Kirim data pembayaran yang sudah dipaginasi ke view
        return view('payments.index', compact('payments'));
    }

    // Menampilkan form untuk menambah pembayaran baru
    public function create()
    {
        return view('payments.create');
    }

    // Menyimpan data pembayaran baru
    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'nama_pembayaran' => 'required|string|max:255',
            'nomor_pembayaran' => 'required|string|max:255',
            'gambar_pembayaran' => 'nullable|image|mimes:jpg,jpeg,png|max:2048', // Jika gambar ada
        ]);

        // Menyimpan data pembayaran
        $payment = new Payment;
        $payment->nama_pembayaran = $request->nama_pembayaran;
        $payment->nomor_pembayaran = $request->nomor_pembayaran;

        // Proses upload gambar jika ada
        if ($request->hasFile('gambar_pembayaran')) {
            $gambarPath = $request->file('gambar_pembayaran')->store('payment_images', 'public');
            $payment->gambar_pembayaran = $gambarPath;
        }

        // Simpan ke database
        $payment->save();

        // Redirect ke halaman daftar pembayaran
        return redirect()->route('payments.index')->with('success', 'Pembayaran berhasil dibuat.');
    }

    // Menampilkan form untuk mengedit pembayaran
    public function edit($id)
    {
        $payment = Payment::findOrFail($id); // Cari pembayaran berdasarkan ID
        return view('payments.edit', compact('payment'));
    }

    // Mengupdate data pembayaran
    public function update(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'nama_pembayaran' => 'required|string|max:255',
            'nomor_pembayaran' => 'required|string|max:255',
            'gambar_pembayaran' => 'nullable|image|mimes:jpg,jpeg,png|max:2048', // Jika gambar ada
        ]);

        // Cari data pembayaran yang akan diupdate
        $payment = Payment::findOrFail($id);

        // Update data pembayaran
        $payment->nama_pembayaran = $request->nama_pembayaran;
        $payment->nomor_pembayaran = $request->nomor_pembayaran;

        // Proses upload gambar jika ada
        if ($request->hasFile('gambar_pembayaran')) {
            // Hapus gambar lama jika ada
            if ($payment->gambar_pembayaran) {
                \Storage::delete('public/' . $payment->gambar_pembayaran);
            }

            // Simpan gambar baru
            $gambarPath = $request->file('gambar_pembayaran')->store('payment_images', 'public');
            $payment->gambar_pembayaran = $gambarPath;
        }

        // Simpan perubahan ke database
        $payment->save();

        // Redirect ke halaman daftar pembayaran
        return redirect()->route('payments.index')->with('success', 'Pembayaran berhasil diupdate.');
    }
    // Menghapus data pembayaran
    public function destroy($id)
    {
        // Cari data pembayaran yang akan dihapus
        $payment = Payment::findOrFail($id);

        // Hapus gambar dari storage jika ada
        if ($payment->gambar_pembayaran) {
            \Storage::delete('public/' . $payment->gambar_pembayaran);
        }

        // Hapus data dari database
        $payment->delete();

        // Redirect ke halaman daftar pembayaran dengan pesan sukses
        return redirect()->route('payments.index')->with('success', 'Pembayaran berhasil dihapus.');
    }
}
