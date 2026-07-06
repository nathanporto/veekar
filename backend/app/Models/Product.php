<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = ['user_id', 'name', 'quantity', 'unit_cost', 'unit_price'];

    protected function casts(): array
    {
        return [
            'unit_cost'  => 'decimal:2',
            'unit_price' => 'decimal:2',
        ];
    }

    public function movements(): HasMany
    {
        return $this->hasMany(ProductMovement::class);
    }
}
