<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceHistory extends Model
{
    protected $fillable = [
        'vehicle_id',
        'employee_id',
        'service_date',
        'description',
        'mileage',
        'amount',
        'notes',
        'return_date',
        'return_reason',
        'entry_checklist',
        'insurer',
        'claim_number',
        'insurance_status',
        'estimated_delivery',
        'tracking_token',
        'service_status',
        'payment_status',
        'amount_paid',
        'commission_amount',
    ];

    protected function casts(): array
    {
        return [
            'service_date'    => 'date',
            'return_date'         => 'date',
            'estimated_delivery'  => 'date',
            'amount'          => 'decimal:2',
            'amount_paid'     => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'entry_checklist' => 'array',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ServiceItem::class);
    }
}
