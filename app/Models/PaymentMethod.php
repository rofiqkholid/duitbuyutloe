<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'account_number',
        'account_name',
        'qr_code_image',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Sensor nomor rekening: sensor awalnya dengan X, sisakan 3 karakter/digit terakhir.
     * Contoh: "8210987654" -> "XXXXXXX654"
     */
    public function getMaskedAccountNumberAttribute(): string
    {
        $num = (string) ($this->account_number ?? '');
        if (empty($num)) {
            return '-';
        }
        $len = strlen($num);
        if ($len <= 3) {
            return $num;
        }
        return str_repeat('X', $len - 3) . substr($num, -3);
    }

    /**
     * Sensor atas nama dengan format per kata, contoh: ROFXX KHOXXX
     * Menyisakan sebagian huruf awal per kata lalu sisanya disensor dengan X.
     */
    public function getMaskedAccountNameAttribute(): string
    {
        $name = trim((string) ($this->account_name ?? ''));
        if (empty($name)) {
            return 'XXX';
        }

        $words = preg_split('/\s+/', $name);
        $maskedWords = [];

        foreach ($words as $word) {
            $len = strlen($word);
            if ($len <= 2) {
                $maskedWords[] = strtoupper($word);
            } elseif ($len <= 4) {
                // Contoh: AMIN -> AMXX
                $maskedWords[] = strtoupper(substr($word, 0, 2)) . str_repeat('X', $len - 2);
            } else {
                // Contoh: ROFIQ (5) -> ROFXX, KURNIAWAN (9) -> KURXXXXXX
                $visible = (int) ceil($len / 2);
                $maskedWords[] = strtoupper(substr($word, 0, $visible)) . str_repeat('X', $len - $visible);
            }
        }

        return implode(' ', $maskedWords);
    }
}
