<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quote extends Model
{
    protected $fillable = ['user_id', 'customer_id', 'vehicle_id', 'token', 'status', 'notes', 'expires_at'];

    protected $casts = ['expires_at' => 'datetime'];

    public function items(): HasMany
    {
        return $this->hasMany(QuoteItem::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
