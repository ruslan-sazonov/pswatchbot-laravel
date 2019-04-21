<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessengerUser extends Model
{

    protected $fillable = [
        'recipient_id',
        'first_name',
        'last_name',
        'locale',
        'timezone'
    ];

    public function watchlist()
    {
        return $this->hasMany('App\Models\MessengerWatchItem', 'recipient_id', 'recipient_id');
    }

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
