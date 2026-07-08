<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentReminder extends Model
{
    protected $fillable = ['user_id', 'description', 'amount', 'due_date', 'paid', 'paid_at'];

    protected function casts(): array
    {
        return [
            'amount'  => 'decimal:2',
            'due_date' => 'date',
            'paid'    => 'boolean',
            'paid_at' => 'datetime',
        ];
    }
}
