<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    protected $fillable = ['user_id', 'customer_id', 'brand', 'model', 'year', 'color', 'plate', 'mileage'];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function serviceHistories(): HasMany
    {
        return $this->hasMany(ServiceHistory::class)->orderByDesc('service_date');
    }
}
