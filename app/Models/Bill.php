<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bill extends Model
{
    use HasFactory;

    protected $fillable = [
        'payer_id',
        'title',
        'type',
        'category',
        'total_amount',
        'description',
        'invoice_image',
        'bill_date',
        'due_date',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'bill_date' => 'date',
            'due_date' => 'date',
        ];
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payer_id');
    }

    public function shares(): HasMany
    {
        return $this->hasMany(BillShare::class, 'bill_id');
    }

    public function getShareAmountAttribute()
    {
        $count = $this->shares()->count();
        return $count > 0 ? $this->total_amount / $count : $this->total_amount;
    }
}
