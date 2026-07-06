<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductMovement extends Model
{
    protected $fillable = [
        'product_id',
        'service_history_id',
        'type',
        'quantity',
        'unit_cost',
        'unit_price',
        'movement_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'unit_cost'     => 'decimal:2',
            'unit_price'    => 'decimal:2',
            'movement_date' => 'date',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function serviceHistory(): BelongsTo
    {
        return $this->belongsTo(ServiceHistory::class);
    }
}
