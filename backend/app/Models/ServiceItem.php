<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceItem extends Model
{
    protected $fillable = ['service_history_id', 'description', 'quantity', 'unit_price'];

    protected function casts(): array
    {
        return [
            'quantity'   => 'decimal:3',
            'unit_price' => 'decimal:2',
        ];
    }

    public function serviceHistory(): BelongsTo
    {
        return $this->belongsTo(ServiceHistory::class);
    }

    public function getSubtotalAttribute(): float
    {
        return (float) $this->quantity * (float) $this->unit_price;
    }
}
