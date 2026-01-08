<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'description',
        'note',
        'unit_price',
        'unit_measurement',
        'quantity',
        'discount',
        'total',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'quantity' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Calculate and set the total amount
     */
    public function calculateTotal(): void
    {
        $subtotal = $this->unit_price * $this->quantity;
        $this->total = max(0, $subtotal - ($this->discount ?? 0));
    }

    /**
     * Boot the model and auto-calculate total
     */
    protected static function booted()
    {
        static::saving(function ($item) {
            $item->calculateTotal();
        });
    }
}
