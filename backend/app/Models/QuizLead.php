<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizLead extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'business_type',
        'cars_per_month',
        'current_control',
        'main_pain',
        'chosen_path',
        'accepted_terms_at',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'accepted_terms_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
