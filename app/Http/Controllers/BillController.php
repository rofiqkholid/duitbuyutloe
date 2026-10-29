<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\BillShare;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BillController extends Controller
{
    public function index()
    {
        $users = User::all();
        $currentUser = Auth::user();

        $bills = Bill::with(['payer.paymentMethods', 'shares.user'])
            ->latest()
            ->paginate(10);

        return view('bills.index', compact('bills', 'users', 'currentUser'));
    }

    public function create()
    {
        $users = User::all();
        $currentUser = Auth::user();

        return view('bills.create', compact('users', 'currentUser'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'payer_id' => 'nullable|exists:users,id',
            'title' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:50',
            'category' => 'nullable|string|max:50',
            'total_amount' => 'required|numeric|min:1000',
            'bill_date' => 'required|date',
            'due_date' => 'nullable|date',
            'description' => 'nullable|string',
            'invoice_image' => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:2048',
            'split_users' => 'nullable|array',
            'custom_amounts' => 'nullable|array',
        ], [
            'invoice_image.max' => 'Ukuran file faktur maksimal 2MB.',
            'invoice_image.mimes' => 'Format file harus berupa gambar (JPG, PNG, WEBP) atau PDF.',
            'invoice_image.uploaded' => 'File gagal diunggah karena melebihi batas upload server (maksimal 2MB)',
        ]);

        $invoicePath = null;
        if ($request->hasFile('invoice_image')) {
            $invoicePath = $request->file('invoice_image')->store('invoices', 'public');
        }

        $typeValue = $request->filled('type') ? $request->type : ($request->category ?: 'wifi');
        $payerId = Auth::id() ?: ($request->payer_id ?: User::first()->id);

        $typeLabels = [
            'wifi' => 'Wifi / Internet',
            'listrik' => 'Listrik / PLN',
            'air' => 'Air PDAM',
            'sampah' => 'Uang Sampah',
            'galon' => 'Galon / Gas',
            'kebersihan' => 'Kebersihan',
            'lainnya' => 'Lainnya',
        ];

        $title = trim($request->title ?? '');
        if (empty($title)) {
            $label = $typeLabels[strtolower($typeValue)] ?? ucfirst($typeValue);
            $title = 'Tagihan ' . $label;
        }

        $bill = Bill::create([
            'payer_id' => $payerId,
            'title' => $title,
            'type' => $typeValue,
            'category' => $typeValue,
            'total_amount' => $request->total_amount,
            'description' => $request->description,
            'invoice_image' => $invoicePath,
            'bill_date' => $request->bill_date,
            'due_date' => $request->due_date,
        ]);

        $allUsers = User::all();
        $isLainnya = strtolower($typeValue) === 'lainnya';

        if ($isLainnya && $request->has('split_users')) {
            $selectedUserIds = (array) $request->split_users;
            $customAmounts = $request->input('custom_amounts', []);

            foreach ($selectedUserIds as $userId) {
                $isPayer = ($userId == $bill->payer_id);
                // Cek nominal manual dari input
                $rawAmount = isset($customAmounts[$userId]) ? str_replace(['.', ','], '', $customAmounts[$userId]) : 0;
                $userAmount = is_numeric($rawAmount) && $rawAmount > 0 ? (float) $rawAmount : 0;

                BillShare::create([
                    'bill_id' => $bill->id,
                    'user_id' => $userId,
                    'amount' => $userAmount,
                    'status' => $isPayer ? 'approved' : 'unpaid',
                    'paid_at' => $isPayer ? now() : null,
                    'notes' => $isPayer ? 'Pembayar tagihan utama (talangan)' : null,
                ]);
            }
        } else {
            $selectedUserIds = $request->split_users ?: $allUsers->pluck('id')->toArray();
            $userCount = count($selectedUserIds);

            if ($userCount > 0) {
                $splitAmount = round($bill->total_amount / $userCount, 2);

                foreach ($selectedUserIds as $userId) {
                    $isPayer = ($userId == $bill->payer_id);
                    BillShare::create([
                        'bill_id' => $bill->id,
                        'user_id' => $userId,
                        'amount' => $splitAmount,
                        'status' => $isPayer ? 'approved' : 'unpaid',
                        'paid_at' => $isPayer ? now() : null,
                        'notes' => $isPayer ? 'Pembayar tagihan utama (talangan)' : null,
                    ]);
                }
            }
        }

        return redirect()->route('bills.show', $bill->id)->with('success', 'Faktur tagihan berhasil ditambahkan!');
    }

    public function show($id)
    {
        $bill = Bill::with(['payer.paymentMethods', 'shares.user'])->findOrFail($id);
        $users = User::all();
        $currentUser = Auth::user();

        return view('bills.show', compact('bill', 'users', 'currentUser'));
    }

    public function edit($id)
    {
        $bill = Bill::findOrFail($id);

        if (!Auth::check() || $bill->payer_id !== Auth::id()) {
            return redirect()->route('bills.show', $bill->id)->with('error', 'Hanya pembuat/penalang faktur yang dapat mengedit faktur ini.');
        }

        $currentUser = Auth::user();
        $users = User::all();

        return view('bills.edit', compact('bill', 'currentUser', 'users'));
    }

    public function update(Request $request, $id)
    {
        $bill = Bill::findOrFail($id);

        if (!Auth::check() || $bill->payer_id !== Auth::id()) {
            return redirect()->route('bills.show', $bill->id)->with('error', 'Hanya pembuat/penalang faktur yang dapat mengedit faktur ini.');
        }

        $request->validate([
            'title' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:50',
            'category' => 'nullable|string|max:50',
            'total_amount' => 'required|numeric|min:1000',
            'bill_date' => 'required|date',
            'due_date' => 'nullable|date',
            'description' => 'nullable|string',
            'invoice_image' => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:2048',
            'split_users' => 'nullable|array',
            'custom_amounts' => 'nullable|array',
        ], [
            'invoice_image.max' => 'Ukuran file faktur maksimal 2MB.',
            'invoice_image.mimes' => 'Format file harus berupa gambar (JPG, PNG, WEBP) atau PDF.',
            'invoice_image.uploaded' => 'File gagal diunggah karena melebihi batas upload server (maksimal 2MB).',
        ]);

        $invoicePath = $bill->invoice_image;
        if ($request->hasFile('invoice_image')) {
            if ($invoicePath && Storage::disk('public')->exists($invoicePath)) {
                Storage::disk('public')->delete($invoicePath);
            }
            $invoicePath = $request->file('invoice_image')->store('invoices', 'public');
        }

        $typeValue = $request->filled('type') ? $request->type : ($bill->type ?: 'wifi');

        $typeLabels = [
            'wifi' => 'Wifi / Internet',
            'listrik' => 'Listrik / PLN',
            'air' => 'Air PDAM',
            'galon' => 'Galon / Gas',
            'kebersihan' => 'Kebersihan',
            'lainnya' => 'Lainnya',
        ];

        $title = trim($request->title ?? '');
        if (empty($title)) {
            $label = $typeLabels[strtolower($typeValue)] ?? ucfirst($typeValue);
            $title = 'Tagihan ' . $label;
        }

        $bill->update([
            'title' => $title,
            'type' => $typeValue,
            'category' => $typeValue,
            'total_amount' => $request->total_amount,
            'description' => $request->description,
            'invoice_image' => $invoicePath,
            'bill_date' => $request->bill_date,
            'due_date' => $request->due_date,
        ]);

        // Recalculate or update split shares
        $isLainnya = strtolower($typeValue) === 'lainnya';

        if ($isLainnya && $request->has('split_users')) {
            $selectedUserIds = (array) $request->split_users;
            $customAmounts = $request->input('custom_amounts', []);

            // Hapus share user yang tidak terpilih
            $bill->shares()->whereNotIn('user_id', $selectedUserIds)->delete();

            foreach ($selectedUserIds as $userId) {
                $isPayer = ($userId == $bill->payer_id);
                $rawAmount = isset($customAmounts[$userId]) ? str_replace(['.', ','], '', $customAmounts[$userId]) : 0;
                $userAmount = is_numeric($rawAmount) && $rawAmount > 0 ? (float) $rawAmount : 0;

                $existingShare = $bill->shares()->where('user_id', $userId)->first();
                if ($existingShare) {
                    $existingShare->update(['amount' => $userAmount]);
                } else {
                    BillShare::create([
                        'bill_id' => $bill->id,
                        'user_id' => $userId,
                        'amount' => $userAmount,
                        'status' => $isPayer ? 'approved' : 'unpaid',
                        'paid_at' => $isPayer ? now() : null,
                        'notes' => $isPayer ? 'Pembayar tagihan utama (talangan)' : null,
                    ]);
                }
            }
        } else {
            $sharesCount = $bill->shares()->count();
            if ($sharesCount > 0) {
                $splitAmount = round($bill->total_amount / $sharesCount, 2);
                $bill->shares()->update(['amount' => $splitAmount]);
            }
        }

        return redirect()->route('bills.show', $bill->id)->with('success', 'Faktur tagihan berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $bill = Bill::findOrFail($id);

        if (!Auth::check() || $bill->payer_id !== Auth::id()) {
            return redirect()->route('bills.show', $bill->id)->with('error', 'Hanya pembuat/penalang faktur yang dapat menghapus faktur ini.');
        }

        if ($bill->invoice_image && Storage::disk('public')->exists($bill->invoice_image)) {
            Storage::disk('public')->delete($bill->invoice_image);
        }
        $bill->delete();

        return redirect()->route('bills.index')->with('success', 'Faktur tagihan berhasil dihapus!');
    }
}
