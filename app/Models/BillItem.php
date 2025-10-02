<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillItem extends Model
{
    use HasFactory;

    protected $table = 'bills_items';

    protected $fillable = [
        'bill_id',
        'description',
        'unit_price',
        'unit_measurement',
        'quantity',
        'total',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'quantity' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    /**
     * Calculate and set the total amount
     */
    public function calculateTotal(): float
    {
        $this->total = $this->unit_price * $this->quantity;
        return $this->total;
    }

    /**
     * Boot the model and auto-calculate total
     */
    protected static function booted()
    {
        static::saving(function ($item) {
            $item->calculateTotal();
        });

        static::created(function ($item) {
            $item->bill->updateTotalAmount();
        });

        static::updated(function ($item) {
            $item->bill->updateTotalAmount();
        });

        static::deleted(function ($item) {
            $item->bill->updateTotalAmount();
        });
    }
}
