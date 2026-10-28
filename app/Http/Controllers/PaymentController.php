<?php

namespace App\Http\Controllers;

use App\Models\BillShare;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    /**
     * Konfirmasi atau upload bukti transfer dari anak kontrakan
     */
    public function payShare(Request $request, $id)
    {
        $share = BillShare::with('bill.payer')->findOrFail($id);

        $request->validate([
            'proof_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'notes' => 'nullable|string|max:255',
        ]);

        $proofPath = $share->proof_image;
        if ($request->hasFile('proof_image')) {
            if ($proofPath && Storage::disk('public')->exists($proofPath)) {
                Storage::disk('public')->delete($proofPath);
            }
            $proofPath = $request->file('proof_image')->store('payment_proofs', 'public');
        }

        $share->update([
            'status' => 'paid',
            'paid_at' => now(),
            'proof_image' => $proofPath,
            'notes' => $request->notes ?: 'Telah ditransfer ke rekening ' . $share->bill->payer->name,
        ]);

        return redirect()->back()->with('success', 'Bukti bayar berhasil dikirim! Menunggu konfirmasi dari ' . $share->bill->payer->name);
    }

    /**
     * Pembayar utama menyetujui (approve) bahwa uang sudah masuk rekeningnya
     */
    public function approveShare(Request $request, $id)
    {
        $share = BillShare::with('bill')->findOrFail($id);

        $share->update([
            'status' => 'approved',
            'paid_at' => $share->paid_at ?: now(),
        ]);

        return redirect()->back()->with('success', 'Pembayaran oleh ' . $share->user->name . ' telah diverifikasi LUNAS!');
    }

    /**
     * Update metode pembayaran rekening bank/e-wallet user
     */
    public function updatePaymentMethod(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|string|max:50',
            'account_number' => 'required|string|max:100',
            'account_name' => 'required|string|max:150',
            'qr_code_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $payment = PaymentMethod::firstOrNew(['user_id' => $request->user_id]);

        $qrPath = $payment->qr_code_image;
        if ($request->hasFile('qr_code_image')) {
            if ($qrPath && Storage::disk('public')->exists($qrPath)) {
                Storage::disk('public')->delete($qrPath);
            }
            $qrPath = $request->file('qr_code_image')->store('qrcodes', 'public');
        }

        $payment->type = $request->type;
        $payment->account_number = $request->account_number;
        $payment->account_name = $request->account_name;
        $payment->qr_code_image = $qrPath;
        $payment->save();

        return redirect()->back()->with('success', 'Data rekening / e-wallet berhasil disimpan!');
    }
}
