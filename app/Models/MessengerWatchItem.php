<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessengerWatchItem extends Model
{

    /**
     * The attributes that can be filled with values.
     *
     * @var array
     */
    protected $fillable = [
        'recipient_id',
        'product_id',
        'name',
        'image',
        'store_url',
        'api_url',
        'prices',
        'fetched_at'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'fetched_at',
    ];

    /**
     * @param $value
     * @return array
     */
    public function getPricesAttribute($value): array
    {
        return json_decode($value, true);
    }

    /**
     * @param $value
     */
    public function setPricesAttribute($value): void
    {
        $this->attributes['prices'] = json_encode($value);
    }

    public function user()
    {
        return $this->belongsTo('App\Models\MessengerUser', 'recipient_id', 'recipient_id');
    }
}
