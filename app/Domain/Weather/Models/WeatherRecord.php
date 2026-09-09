<?php

namespace App\Domain\Weather\Models;

use App\Domain\Rides\Models\Ride;
use Database\Factories\WeatherRecordFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeatherRecord extends Model
{
    /** @use HasFactory<WeatherRecordFactory> */
    use HasFactory;

    protected $fillable = [
        'ride_id',
        'temperature_c',
        'apparent_temperature_c',
        'precipitation_mm',
        'rain_mm',
        'snowfall_cm',
        'cloud_cover_percent',
        'wind_speed_kmh',
        'wind_gusts_kmh',
        'wind_direction_degrees',
        'relative_humidity_percent',
        'weather_code',
        'observed_at',
    ];

    /**
     * @return BelongsTo<Ride, $this>
     */
    public function ride(): BelongsTo
    {
        return $this->belongsTo(Ride::class, 'ride_id', 'ride_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ride_id' => 'integer',
            'temperature_c' => 'float',
            'apparent_temperature_c' => 'float',
            'precipitation_mm' => 'float',
            'rain_mm' => 'float',
            'snowfall_cm' => 'float',
            'cloud_cover_percent' => 'float',
            'wind_speed_kmh' => 'float',
            'wind_gusts_kmh' => 'float',
            'wind_direction_degrees' => 'float',
            'relative_humidity_percent' => 'float',
            'weather_code' => 'integer',
            'observed_at' => 'datetime',
        ];
    }
}
