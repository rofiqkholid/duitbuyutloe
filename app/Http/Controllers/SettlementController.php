<?php

namespace App\Http\Controllers;

use App\Models\BillShare;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SettlementController extends Controller
{
    /**
     * Tampilkan halaman kalkulasi dan otomatis kompensasi / potong hutang timbal balik
     */
    public function show($shareId)
    {
        $share = BillShare::with(['bill.payer.paymentMethods', 'bill.shares.user', 'user'])->findOrFail($shareId);
        $currentUser = Auth::user();

        // Validasi hak akses: hanya orang yang bersangkutan yang boleh melihat
        if ($share->user_id !== $currentUser->id && $share->bill->payer_id !== $currentUser->id) {
            abort(403, 'Kamu tidak memiliki akses ke tagihan ini.');
        }

        // Tentukan siapa "Saya" dan siapa "Lawan Transaksi"
        $isDebtor = ($share->user_id === $currentUser->id);
        $me = $currentUser;
        $otherUser = $isDebtor ? $share->bill->payer : $share->user;

        // 1. Cek hutang aktif yang belum lunas
        $myDebtsActive = BillShare::where('user_id', $me->id)
            ->where('status', '!=', 'approved')
            ->whereHas('bill', function ($q) use ($otherUser) {
                $q->where('payer_id', $otherUser->id);
            })
            ->get();

        $otherDebtsActive = BillShare::where('user_id', $otherUser->id)
            ->where('status', '!=', 'approved')
            ->whereHas('bill', function ($q) use ($me) {
                $q->where('payer_id', $me->id);
            })
            ->get();

        $totalMyDebtActive = (float) $myDebtsActive->sum('amount');
        $totalOtherDebtActive = (float) $otherDebtsActive->sum('amount');

        $justOffsetAmount = 0;

        // 2. Eksekusi pemotongan otomatis jika kedua belah pihak sama-sama punya hutang aktif
        if ($totalMyDebtActive > 0 && $totalOtherDebtActive > 0) {
            $justOffsetAmount = $this->performOffset($share, $me, $otherUser);

            if ($justOffsetAmount > 0) {
                $formattedOffset = number_format($justOffsetAmount, 0, ',', '.');
                session()->flash('success', "⚡ Hutang timbal balik sebesar Rp {$formattedOffset} telah otomatis dipotong!");
                $share->refresh();
            }
        }

        // Ambil sisa hutang aktif saat ini setelah pemotongan
        $myDebtsRemaining = BillShare::where('user_id', $me->id)
            ->where('status', '!=', 'approved')
            ->whereHas('bill', function ($q) use ($otherUser) {
                $q->where('payer_id', $otherUser->id);
            })
            ->get();

        $otherDebtsRemaining = BillShare::where('user_id', $otherUser->id)
            ->where('status', '!=', 'approved')
            ->whereHas('bill', function ($q) use ($me) {
                $q->where('payer_id', $me->id);
            })
            ->get();

        $offsetAmount = $justOffsetAmount;

        // Jika baru saja terjadi pemotongan di request ini, tampilkan angka sebelum dipotong
        // Jika tidak ada pemotongan aktif baru (misal user lain tidak punya hutang), tampilkan angka riil saat ini
        if ($offsetAmount > 0) {
            $totalMyDebt = (float) $myDebtsRemaining->sum('amount') + $offsetAmount;
            $totalOtherDebt = (float) $otherDebtsRemaining->sum('amount') + $offsetAmount;
        } else {
            $totalMyDebt = (float) $myDebtsRemaining->sum('amount');
            $totalOtherDebt = (float) $otherDebtsRemaining->sum('amount');
        }

        $netBalance = $totalMyDebt - $totalOtherDebt;

        $countMyDebt = max(1, $myDebtsRemaining->count());
        $countOtherDebt = max(1, $otherDebtsRemaining->count());

        $paymentMethod = $otherUser->paymentMethods->first();

        return view('settlements.show', compact(
            'share',
            'me',
            'otherUser',
            'totalMyDebt',
            'totalOtherDebt',
            'offsetAmount',
            'netBalance',
            'countMyDebt',
            'countOtherDebt',
            'paymentMethod'
        ));
    }

    /**
     * Konfirmasi pelunasan via potong hutang
     */
    public function confirm(Request $request, $shareId)
    {
        $share = BillShare::with(['bill.payer', 'user'])->findOrFail($shareId);
        $currentUser = Auth::user();

        if ($share->user_id !== $currentUser->id && $share->bill->payer_id !== $currentUser->id) {
            abort(403, 'Akses ditolak.');
        }

        $isDebtor = ($share->user_id === $currentUser->id);
        $me = $currentUser;
        $otherUser = $isDebtor ? $share->bill->payer : $share->user;

        $otherRemaining = (float) BillShare::where('user_id', $otherUser->id)
            ->where('status', '!=', 'approved')
            ->whereHas('bill', fn($q) => $q->where('payer_id', $me->id))
            ->sum('amount');

        $formattedRemaining = number_format($otherRemaining, 0, ',', '.');
        $customNote = $request->notes ? trim($request->notes) : "Konfirmasi lunas via kompensasi potong hutang dengan {$otherUser->name}.";
        $prefix = $share->notes ? $share->notes . ' | ' : '';

        $share->update([
            'status' => 'approved',
            'paid_at' => $share->paid_at ?: now(),
            'notes' => $prefix . $customNote,
        ]);

        $message = "Konfirmasi berhasil! Hutang kamu lunas, dan sisa hutang {$otherUser->name} ke kamu adalah Rp {$formattedRemaining}.";
        if ($otherRemaining <= 0) {
            $message = "Konfirmasi berhasil! Hutang kamu telah terverifikasi lunas.";
        }

        return redirect()->route('dashboard')->with('success', $message);
    }

    /**
     * Helper pemotongan hutang timbal balik (Netting / Kompensasi)
     */
    private function performOffset($share, $me, $otherUser): float
    {
        $offsetAmount = 0;

        DB::transaction(function () use ($share, $me, $otherUser, &$offsetAmount) {
            // Ambil semua hutang 'Saya' ke 'Lawan Transaksi' dengan row lock
            $myDebts = BillShare::where('user_id', $me->id)
                ->where('status', '!=', 'approved')
                ->whereHas('bill', function ($q) use ($otherUser) {
                    $q->where('payer_id', $otherUser->id);
                })
                ->orderByRaw("CASE WHEN id = {$share->id} THEN 0 ELSE 1 END")
                ->orderBy('created_at', 'asc')
                ->lockForUpdate()
                ->get();

            // Ambil semua hutang 'Lawan Transaksi' ke 'Saya' dengan row lock
            $otherDebts = BillShare::where('user_id', $otherUser->id)
                ->where('status', '!=', 'approved')
                ->whereHas('bill', function ($q) use ($me) {
                    $q->where('payer_id', $me->id);
                })
                ->orderBy('created_at', 'asc')
                ->lockForUpdate()
                ->get();

            $totalMyDebt = (float) $myDebts->sum('amount');
            $totalOtherDebt = (float) $otherDebts->sum('amount');

            if ($totalMyDebt <= 0 || $totalOtherDebt <= 0) {
                return;
            }

            $offsetAmount = min($totalMyDebt, $totalOtherDebt);
            $remMine = $offsetAmount;
            $remOther = $offsetAmount;

            // 1. Kurangi / lunaskan hutang saya
            foreach ($myDebts as $myShare) {
                if ($remMine <= 0) break;

                if ($myShare->amount <= $remMine) {
                    $deducted = (float) $myShare->amount;
                    $remMine -= $deducted;
                    $prefix = $myShare->notes ? $myShare->notes . ' | ' : '';
                    $myShare->update([
                        'status' => 'approved',
                        'paid_at' => now(),
                        'notes' => $prefix . 'Lunas otomatis via kompensasi potong hutang dengan ' . $otherUser->name . ' senilai Rp ' . number_format($deducted, 0, ',', '.'),
                    ]);
                } else {
                    $deducted = $remMine;
                    $newAmount = (float) $myShare->amount - $deducted;
                    $remMine = 0;
                    $prefix = $myShare->notes ? $myShare->notes . ' | ' : '';
                    $myShare->update([
                        'amount' => $newAmount,
                        'notes' => $prefix . 'Dipotong Rp ' . number_format($deducted, 0, ',', '.') . ' via kompensasi hutang ' . $otherUser->name . ' (Sisa tagihan: Rp ' . number_format($newAmount, 0, ',', '.') . ')',
                    ]);
                }
            }

            // 2. Kurangi / lunaskan hutang lawan transaksi
            foreach ($otherDebts as $otherShare) {
                if ($remOther <= 0) break;

                if ($otherShare->amount <= $remOther) {
                    $deducted = (float) $otherShare->amount;
                    $remOther -= $deducted;
                    $prefix = $otherShare->notes ? $otherShare->notes . ' | ' : '';
                    $otherShare->update([
                        'status' => 'approved',
                        'paid_at' => now(),
                        'notes' => $prefix . 'Lunas otomatis via kompensasi potong hutang dengan ' . $me->name . ' senilai Rp ' . number_format($deducted, 0, ',', '.'),
                    ]);
                } else {
                    $deducted = $remOther;
                    $newAmount = (float) $otherShare->amount - $deducted;
                    $remOther = 0;
                    $prefix = $otherShare->notes ? $otherShare->notes . ' | ' : '';
                    $otherShare->update([
                        'amount' => $newAmount,
                        'notes' => $prefix . 'Dipotong Rp ' . number_format($deducted, 0, ',', '.') . ' via kompensasi hutang ' . $me->name . ' (Sisa tagihan: Rp ' . number_format($newAmount, 0, ',', '.') . ')',
                    ]);
                }
            }
        });

        return (float) $offsetAmount;
    }
}
