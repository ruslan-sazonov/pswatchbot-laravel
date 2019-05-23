<?php

namespace App\Models;

use Illuminate\Support\Collection;
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

    /**
     * @param int $id
     * @return MessengerUser
     */
    public static function getSingle(int $id): MessengerUser
    {
        return self::where('recipient_id', $id)->first();
    }

    /**
     * @param int $id
     * @param int $batchSize
     * @return \Illuminate\Support\Collection
     */
    public static function getWatchlist(int $id, int $batchSize = 10): Collection
    {
        return self::getSingle($id)
            ->watchlist()
            ->get();
    }
}
