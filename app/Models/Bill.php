<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bill extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vendor_id',
        'bill_number',
        'bill_date',
        'due_date',
        'amount',
        'amount_paid',
        'status',
        'bill_type',
        'description',
        'recurring_frequency',
        'next_due_date',
        'image_path',
        'created_by',
    ];

    protected $casts = [
        'bill_date' => 'date',
        'due_date' => 'date',
        'next_due_date' => 'date',
        'amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(BillPayment::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BillItem::class);
    }

    public function updateTotalAmount(): void
    {
        $this->amount = $this->items->sum(function ($item) {
            return $item->unit_price * $item->quantity;
        });
        $this->save();
    }

    public function updateStatus(): void
    {
        if ($this->amount_paid >= $this->amount) {
            $this->status = 'paid';
        } elseif ($this->amount_paid > 0) {
            $this->status = 'partially_paid';
        } elseif ($this->due_date && $this->due_date < now() && $this->status !== 'paid') {
            $this->status = 'overdue';
        }

        $this->save();
    }
}
