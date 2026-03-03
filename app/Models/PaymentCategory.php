<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'value',
        'label',
        'description',
        'description_placeholder',
    ];

    public function chequePayments(): HasMany
    {
        return $this->hasMany(ChequePayment::class, 'payment_category', 'value');
    }
}
