<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpenseClaim extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'reference_id',
        'claim_date',
        'title',
        'category',
        'receipt_image_path',
        'total',
        'payee',
        'status',
        'expense_type',
        'notes',
        'transaction_id',
        'branch_id',
    ];

    protected $casts = [
        'claim_date' => 'date',
        'total' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }



    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ExpenseItem::class);
    }

    public function updateTotalAmount(): void
    {
        $this->total = $this->items->sum(function ($item) {
            return $item->unit_price * $item->quantity;
        });
        $this->save();
    }
}