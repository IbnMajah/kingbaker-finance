<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleCreditItem extends Model
{
    use HasFactory;

    protected $table = 'sales_credit_items';

    protected $fillable = [
        'sale_id',
        'customer_id',
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

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'customer_id');
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
