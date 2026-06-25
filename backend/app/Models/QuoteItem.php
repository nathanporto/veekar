<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteItem extends Model
{
    protected $fillable = ['quote_id', 'description', 'quantity', 'unit_price'];

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }
}
