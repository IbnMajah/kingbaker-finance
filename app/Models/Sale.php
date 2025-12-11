<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sale extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'branch_id',
        'shift_id',
        'sales_date',
        'amount', // This represents depositable_amount
        'total_amount',
        'cash_amount',
        'bank_transfer_amount',
        'mobile_money_my_nafa',
        'mobile_money_aps',
        'mobile_money_wave',
        'cashier',
        'closing_manager'
    ];

    protected $casts = [
        'sales_date' => 'date',
        'amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'cash_amount' => 'decimal:2',
        'bank_transfer_amount' => 'decimal:2',
        'mobile_money_my_nafa' => 'decimal:2',
        'mobile_money_aps' => 'decimal:2',
        'mobile_money_wave' => 'decimal:2',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function deposits()
    {
        return $this->belongsToMany(Deposit::class, 'deposit_sales');
    }

    public function creditItems()
    {
        return $this->hasMany(SaleCreditItem::class);
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('amount', 'like', '%' . $search . '%')
                    ->orWhereHas('branch', function ($query) use ($search) {
                        $query->where('name', 'like', '%' . $search . '%');
                    });
            });
        })->when($filters['trashed'] ?? null, function ($query, $trashed) {
            if ($trashed === 'with') {
                $query->withTrashed();
            } elseif ($trashed === 'only') {
                $query->onlyTrashed();
            }
        });
    }

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? 'id', $value)->withTrashed()->firstOrFail();
    }
}
