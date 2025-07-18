<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'location',
        'phone',
        'email',
        'manager_name',
        'description',
        'daily_sales_target',
        'monthly_sales_target',
        'active',
    ];

    protected $casts = [
        'daily_sales_target' => 'decimal:2',
        'monthly_sales_target' => 'decimal:2',
        'daily_progress' => 'decimal:2',
        'monthly_progress' => 'decimal:2',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function expenseClaims(): HasMany
    {
        return $this->hasMany(ExpenseClaim::class);
    }

    public function getDailyProgressAttribute(): float
    {
        if (!$this->daily_sales_target || $this->daily_sales_target <= 0) {
            return 0;
        }

        $dailySales = $this->sales()
            ->whereDate('created_at', today())
            ->sum('amount');

        return min(100, round(($dailySales / $this->daily_sales_target) * 100, 2));
    }

    public function getMonthlyProgressAttribute(): float
    {
        if (!$this->monthly_sales_target || $this->monthly_sales_target <= 0) {
            return 0;
        }

        $monthlySales = $this->sales()
            ->whereYear('created_at', today()->year)
            ->whereMonth('created_at', today()->month)
            ->sum('amount');

        return min(100, round(($monthlySales / $this->monthly_sales_target) * 100, 2));
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('location', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            });
        })->when($filters['status'] ?? null, function ($query, $status) {
            if ($status === 'active') {
                $query->whereNull('deleted_at');
            } elseif ($status === 'inactive') {
                $query->whereNotNull('deleted_at');
            }
        });
    }

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? 'id', $value)->withTrashed()->firstOrFail();
    }
}