<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpenseItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'expense_claim_id',
        'description',
        'unit_price',
        'quantity',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'quantity' => 'integer',
    ];

    protected $attributes = [
        'quantity' => 1,
    ];

    public function expenseClaim(): BelongsTo
    {
        return $this->belongsTo(ExpenseClaim::class);
    }

    protected static function booted()
    {
        static::created(function ($item) {
            $item->expenseClaim->updateTotalAmount();
        });

        static::updated(function ($item) {
            $item->expenseClaim->updateTotalAmount();
        });

        static::deleted(function ($item) {
            $item->expenseClaim->updateTotalAmount();
        });
    }
}