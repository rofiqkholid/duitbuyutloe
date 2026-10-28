<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\BillShare;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with('paymentMethods')->get();

        // User login saat ini (null jika guest)
        $currentUser = Auth::user();

        // Tagihan terbaru
        $recentBills = Bill::with(['payer.paymentMethods', 'shares.user'])
            ->latest()
            ->take(10)
            ->get();

        if ($currentUser) {
            // Tagihan milik user aktif yang belum lunas (harus bayar ke orang lain)
            $myUnpaidShares = BillShare::with(['bill.payer.paymentMethods'])
                ->where('user_id', $currentUser->id)
                ->where('status', '!=', 'approved')
                ->whereHas('bill', function ($q) use ($currentUser) {
                    $q->where('payer_id', '!=', $currentUser->id);
                })
                ->latest()
                ->get();

            // Tagihan yang saya bayar / talangi dan orang lain berhutang ke saya
            $claimsToReceive = BillShare::with(['bill', 'user'])
                ->where('status', '!=', 'approved')
                ->whereHas('bill', function ($q) use ($currentUser) {
                    $q->where('payer_id', $currentUser->id);
                })
                ->where('user_id', '!=', $currentUser->id)
                ->latest()
                ->get();

            $totalMyDebt = $myUnpaidShares->where('status', 'unpaid')->sum('amount');
            $totalToReceive = $claimsToReceive->sum('amount');
        } else {
            // Untuk guest / publik yang belum login
            $myUnpaidShares = collect();
            $claimsToReceive = collect();
            $totalMyDebt = 0;
            $totalToReceive = 0;
        }

        // Statistik umum
        $totalKontrakanBills = Bill::sum('total_amount');
        $allUnpaidBills = BillShare::where('status', 'unpaid')->count();
        $totalUnpaidAmount = BillShare::where('status', 'unpaid')->sum('amount');

        // Cari penghuni dengan utang tertinggi
        $highestDebtorShare = BillShare::with('user')
            ->where('status', 'unpaid')
            ->selectRaw('user_id, sum(amount) as total_debt')
            ->groupBy('user_id')
            ->orderByDesc('total_debt')
            ->first();

        $highestDebtorName = $highestDebtorShare ? $highestDebtorShare->user?->name : 'Nihil';
        $highestDebtorAmount = $highestDebtorShare ? (int)$highestDebtorShare->total_debt : 0;

        return view('dashboard', compact(
            'users',
            'currentUser',
            'recentBills',
            'myUnpaidShares',
            'claimsToReceive',
            'totalMyDebt',
            'totalToReceive',
            'totalKontrakanBills',
            'allUnpaidBills',
            'totalUnpaidAmount',
            'highestDebtorName',
            'highestDebtorAmount'
        ));
    }
}
