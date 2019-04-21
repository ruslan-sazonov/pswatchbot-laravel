<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessengerWatchItem extends Model
{
    protected $fillable = [
        'recipient_id',
        'product_id',
        'name',
        'image',
        'store_url',
        'api_url',
        'fetched_at'
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\MessengerUser', 'recipient_id', 'recipient_id');
    }
}
