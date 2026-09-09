<?php

namespace App\Models;

use App\Domain\Weather\Models\WeatherRecord;
use Database\Factories\RideFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Ride extends Model
{
    /** @use HasFactory<RideFactory> */
    use HasFactory;

    protected $primaryKey = 'ride_id';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'ride_id',
        'account_id',
        'status',
        'duration',
        'bike_number',
        'origin_station_code',
        'origin_station',
        'origin_slot_id',
        'checkout_time',
        'destination_station_code',
        'destination_station',
        'destination_slot_id',
        'checkin_time',
        'distance_checked_at',
        'weather_checked_at',
    ];

    /**
     * @return HasOne<WeatherRecord, $this>
     */
    public function weather(): HasOne
    {
        return $this->hasOne(WeatherRecord::class, 'ride_id', 'ride_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'account_id' => 'integer',
            'duration' => 'integer',
            'checkout_time' => 'datetime',
            'checkin_time' => 'datetime',
            'distance_checked_at' => 'datetime',
            'weather_checked_at' => 'datetime',
        ];
    }
}
