<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserTwoFactorDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'secret',
        'confirmed_at',
    ];

    protected $hidden = [
        'secret',
    ];

    protected $casts = [
        'secret' => 'encrypted',
        'confirmed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
