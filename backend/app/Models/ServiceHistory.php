<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceHistory extends Model
{
    protected $fillable = [
        'vehicle_id',
        'service_date',
        'description',
        'mileage',
        'amount',
        'notes',
        'return_date',
        'return_reason',
        'entry_checklist',
    ];

    protected function casts(): array
    {
        return [
            'service_date'    => 'date',
            'return_date'     => 'date',
            'amount'          => 'decimal:2',
            'entry_checklist' => 'array',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ServiceItem::class);
    }
}
