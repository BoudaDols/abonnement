<?php

namespace App\Model;

class Payment extends BaseModel
{
    protected $fillable = ['subscription_id', 'amount', 'status', 'transaction_id', 'paid_at'];

    protected $casts = [
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}