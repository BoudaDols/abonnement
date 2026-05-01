<?php

namespace App\Model;

class Plan extends BaseModel
{
    protected $fillable = ['name', 'type', 'price', 'duration_days', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function isFree(): bool
    {
        return $this->type === 'free';
    }
}
