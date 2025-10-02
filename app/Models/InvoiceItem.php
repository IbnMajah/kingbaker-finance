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

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Calculate and set the total amount
     */
    public function calculateTotal(): void
    {
        $this->total = $this->unit_price * $this->quantity;
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
