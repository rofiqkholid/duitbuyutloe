<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Bill;
use App\Models\BillShare;
use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 5 User Kontrakan dengan NIK (Nomor Induk Kewarasan) & password sama dengan NIK
        $members = [
            [
                'name' => 'Rofiq',
                'nik' => 'BUBULAK-01',
                'email' => 'rofiq@kontrakan.local',
                'password' => Hash::make('BUBULAK-01'),
                'payment' => [
                    'type' => 'BCA',
                    'account_number' => '8210987654',
                    'account_name' => 'Rofiq Kurniawan',
                ]
            ],
            [
                'name' => 'Amin',
                'nik' => 'BUBULAK-02',
                'email' => 'amin@kontrakan.local',
                'password' => Hash::make('BUBULAK-02'),
                'payment' => [
                    'type' => 'SeaBank / QRIS',
                    'account_number' => '901988776655',
                    'account_name' => 'Amin Rais',
                ]
            ],
            [
                'name' => 'Tangguh',
                'nik' => 'BUBULAK-03',
                'email' => 'tangguh@kontrakan.local',
                'password' => Hash::make('BUBULAK-03'),
                'payment' => [
                    'type' => 'Mandiri',
                    'account_number' => '1370012345678',
                    'account_name' => 'Tangguh Wicaksono',
                ]
            ],
            [
                'name' => 'Reza',
                'nik' => 'BUBULAK-04',
                'email' => 'reza@kontrakan.local',
                'password' => Hash::make('BUBULAK-04'),
                'payment' => [
                    'type' => 'BRI',
                    'account_number' => '034101000123456',
                    'account_name' => 'Reza Fahlevi',
                ]
            ],
            [
                'name' => 'Saeful',
                'nik' => 'BUBULAK-05',
                'email' => 'saeful@kontrakan.local',
                'password' => Hash::make('BUBULAK-05'),
                'payment' => [
                    'type' => 'Dana / Gopay',
                    'account_number' => '081234567890',
                    'account_name' => 'Saeful Bahri',
                ]
            ],
        ];

        $createdUsers = [];

        foreach ($members as $item) {
            $user = User::updateOrCreate(
                ['email' => $item['email']],
                [
                    'name' => $item['name'],
                    'nik' => $item['nik'],
                    'password' => $item['password'],
                ]
            );
            $createdUsers[$item['name']] = $user;

            PaymentMethod::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'type' => $item['payment']['type'],
                    'account_number' => $item['payment']['account_number'],
                    'account_name' => $item['payment']['account_name'],
                ]
            );
        }

        // Contoh tagihan Wifi Rp 100.000 ditalangi oleh Rofiq
        $rofiq = $createdUsers['Rofiq'];
        $bill = Bill::firstOrCreate(
            ['title' => 'Tagihan Wifi Indihome Bulan Ini'],
            [
                'payer_id' => $rofiq->id,
                'category' => 'wifi',
                'total_amount' => 100000.00,
                'description' => 'Faktur tagihan internet wifi bulanan kontrakan. Harap bayar bagian masing-masing ke Rofiq.',
                'invoice_image' => null,
                'bill_date' => now()->toDateString(),
                'due_date' => now()->addDays(5)->toDateString(),
            ]
        );

        $splitAmount = 100000.00 / count($createdUsers);

        foreach ($createdUsers as $name => $user) {
            BillShare::firstOrCreate(
                [
                    'bill_id' => $bill->id,
                    'user_id' => $user->id,
                ],
                [
                    'amount' => $splitAmount,
                    'status' => ($user->id === $rofiq->id) ? 'approved' : 'unpaid',
                    'paid_at' => ($user->id === $rofiq->id) ? now() : null,
                    'notes' => ($user->id === $rofiq->id) ? 'Ditalangi langsung oleh Rofiq' : null,
                ]
            );
        }
    }
}
